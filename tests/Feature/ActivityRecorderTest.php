<?php

use Tapp\FilamentActivity\Models\Activity;
use Tapp\FilamentActivity\Services\ActivityRecorder;
use Tapp\FilamentActivity\Tests\Models\Post;
use Tapp\FilamentActivity\Tests\Models\Team;
use Tapp\FilamentActivity\Tests\Models\User;

it('records activity with actor subject parent tenant and metadata', function (): void {
    $tenant = Team::query()->create(['name' => 'Team A']);
    $actor = User::query()->create(['name' => 'Ada']);
    $post = Post::query()->create(['name' => 'First topic']);
    $parent = Post::query()->create(['name' => 'Parent topic']);

    $activity = app(ActivityRecorder::class)->record(
        event: 'forum.post.created',
        subject: $post,
        actor: $actor,
        tenant: $tenant,
        parent: $parent,
        title: 'Ada posted First topic',
        summary: 'First topic was added to the forum.',
        metadata: ['forum_id' => 123],
    );

    expect($activity)->toBeInstanceOf(Activity::class)
        ->and($activity->event)->toBe('forum.post.created')
        ->and($activity->actor->is($actor))->toBeTrue()
        ->and($activity->subject->is($post))->toBeTrue()
        ->and($activity->parent->is($parent))->toBeTrue()
        ->and($activity->tenant_id)->toBe($tenant->id)
        ->and($activity->metadata)->toBe(['forum_id' => 123])
        ->and($activity->occurred_at)->not->toBeNull();
});

it('scopes activities by tenant', function (): void {
    $teamA = Team::query()->create(['name' => 'Team A']);
    $teamB = Team::query()->create(['name' => 'Team B']);
    $post = Post::query()->create(['name' => 'Topic']);
    $recorder = app(ActivityRecorder::class);

    $teamAActivity = $recorder->record('forum.post.created', $post, tenant: $teamA);
    $recorder->record('forum.post.created', $post, tenant: $teamB);

    expect(Activity::query()->forTenant($teamA)->sole()->id)->toBe($teamAActivity->id);
});

it('scopes activities by actor subject parent event prefix and recency', function (): void {
    $actor = User::query()->create(['name' => 'Ada']);
    $otherActor = User::query()->create(['name' => 'Grace']);
    $post = Post::query()->create(['name' => 'Topic']);
    $otherPost = Post::query()->create(['name' => 'Other topic']);
    $parent = Post::query()->create(['name' => 'Parent topic']);
    $recorder = app(ActivityRecorder::class);

    $matchingActivity = $recorder->record(
        event: 'forum.post.created',
        subject: $post,
        actor: $actor,
        parent: $parent,
    );
    $matchingActivity->forceFill(['occurred_at' => now()->subHour()])->save();

    $recorder->record('course.lesson.completed', $post, actor: $actor, parent: $parent);
    $recorder->record('forum.post.created', $otherPost, actor: $actor, parent: $parent);
    $recorder->record('forum.post.created', $post, actor: $otherActor, parent: $parent);

    expect(Activity::query()->forActor($actor)->count())->toBe(3)
        ->and(Activity::query()->forSubject($post)->count())->toBe(3)
        ->and(Activity::query()->forParent($parent)->count())->toBe(4)
        ->and(Activity::query()->eventPrefix('forum')->count())->toBe(3)
        ->and(Activity::query()->eventPrefix(['forum.post'])->count())->toBe(3)
        ->and(Activity::query()->occurredSince(now()->subMinutes(30))->count())->toBe(3)
        ->and(Activity::query()
            ->forActor($actor)
            ->forSubject($post)
            ->forParent($parent)
            ->eventPrefix('forum')
            ->occurredSince(now()->subHours(2))
            ->sole()
            ->id
        )->toBe($matchingActivity->id);
});

it('exposes subject activities through the trait', function (): void {
    $post = Post::query()->create(['name' => 'Topic']);

    app(ActivityRecorder::class)->record('forum.post.created', $post);

    expect($post->activities()->count())->toBe(1);
});
