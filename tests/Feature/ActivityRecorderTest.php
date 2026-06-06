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

it('exposes subject activities through the trait', function (): void {
    $post = Post::query()->create(['name' => 'Topic']);

    app(ActivityRecorder::class)->record('forum.post.created', $post);

    expect($post->activities()->count())->toBe(1);
});
