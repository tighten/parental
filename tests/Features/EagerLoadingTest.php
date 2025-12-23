<?php

namespace Parental\Tests\Features;

use Parental\Tests\Models\Message;
use Parental\Tests\Models\Room;
use Parental\Tests\Models\TextMessage;
use Parental\Tests\Models\Video;
use Parental\Tests\Models\VideoMessage;
use Parental\Tests\TestCase;

class EagerLoadingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (version_compare(app()->version(), '11.0.0', '<')) {
            $this->markTestSkipped('Eager loading macros are only available in Laravel 11 and above.');
        }
    }

    /** @test */
    public function eager_load_children_on_collection(): void
    {
        $textMessage = TextMessage::create();
        $textMessage->images()->create(['url' => 'https://example.com/image1.jpg']);

        $video = Video::create(['url' => 'https://example.com/video1.mp5']);
        VideoMessage::create(['video_id' => $video->getKey()]);

        $messages = Message::all();
        $messages->loadChildren([
            TextMessage::class => ['images'],
            VideoMessage::class => ['video'],
        ]);

        $this->assertTrue($messages->whereInstanceOf(TextMessage::class)->every->relationLoaded('images'));
        $this->assertTrue($messages->whereInstanceOf(VideoMessage::class)->every->relationLoaded('video'));
    }

    /** @test */
    public function eager_load_children_count_on_collection(): void
    {
        $textMessage = TextMessage::create();
        $textMessage->images()->create(['url' => 'https://example.com/image1.jpg']);

        $video = Video::create(['url' => 'https://example.com/video1.mp5']);
        VideoMessage::create(['video_id' => $video->getKey()]);

        $messages = Message::all();
        $messages->loadChildrenCount([
            TextMessage::class => ['images'],
        ]);

        $this->assertEquals($messages->firstWhere(fn ($message) => $message instanceof TextMessage)->images_count, 1);
    }

    /** @test */
    public function eager_load_children_on_paginator(): void
    {
        $textMessage = TextMessage::create();
        $textMessage->images()->create(['url' => 'https://example.com/image1.jpg']);

        $video = Video::create(['url' => 'https://example.com/video1.mp5']);
        VideoMessage::create(['video_id' => $video->getKey()]);

        $messages = Message::query()->paginate();

        // This call would ideally return back the paginator itself, but since it's being
        // forwarded to the collection, it returns the collection. The paginator isn't
        // macroable, so we can't change it. Use tap if you need the paginator back.
        $messages->loadChildrenCount([
            TextMessage::class => ['images'],
        ]);

        $this->assertEquals($messages->firstWhere(fn ($message) => $message instanceof TextMessage)->images_count, 1);
    }

    /** @test */
    public function eager_load_from_model(): void
    {
        $textMessage = TextMessage::create();
        $textMessage->images()->create(['url' => 'https://example.com/image1.jpg']);

        $message = Message::find($textMessage->getKey());

        $message->loadChildren([
            TextMessage::class => ['images'],
            VideoMessage::class => ['video'],
        ]);

        $this->assertTrue($message->relationLoaded('images'));
    }

    /** @test */
    public function eager_load_counts_on_model(): void
    {
        $textMessage = TextMessage::create();
        $textMessage->images()->create(['url' => 'https://example.com/image1.jpg']);

        $message = Message::find($textMessage->getKey());

        $message->loadChildrenCount([
            TextMessage::class => ['images'],
            VideoMessage::class => ['video'],
        ]);

        $this->assertEquals(1, $message->images_count);
    }

    /** @test */
    public function eager_load_children_from_query(): void
    {
        $textMessage = TextMessage::create();
        $textMessage->images()->create(['url' => 'https://example.com/image1.jpg']);

        $video = Video::create(['url' => 'https://example.com/video1.mp5']);
        VideoMessage::create(['video_id' => $video->getKey()]);

        $messages = Message::query()->childrenWith([
            TextMessage::class => ['images'],
            VideoMessage::class => ['video'],
        ])->get();

        $this->assertCount(2, $messages);
        $this->assertTrue($messages->whereInstanceOf(TextMessage::class)->every->relationLoaded('images'));
        $this->assertTrue($messages->whereInstanceOf(VideoMessage::class)->every->relationLoaded('video'));
    }

    /** @test */
    public function eager_load_children_count_from_query(): void
    {
        $textMessage = TextMessage::create();
        $textMessage->images()->create(['url' => 'https://example.com/image1.jpg']);

        $video = Video::create(['url' => 'https://example.com/video1.mp5']);
        VideoMessage::create(['video_id' => $video->getKey()]);

        $messages = Message::query()->childrenWithCount([
            TextMessage::class => ['images'],
        ])->get();

        $this->assertCount(2, $messages);
        $this->assertEquals(1, $messages->firstWhere(fn ($message) => $message instanceof TextMessage)->images_count);
        $this->assertNull($messages->firstWhere(fn ($message) => $message instanceof VideoMessage)->images_count);
    }

    /** @test */
    public function eager_load_children_from_relationship_query(): void
    {
        $room = Room::create(['name' => 'General']);

        $textMessage = TextMessage::create(['room_id' => $room->getKey()]);
        $textMessage->images()->create(['url' => 'https://example.com/image1.jpg']);

        $video = Video::create(['url' => 'https://example.com/video1.mp5']);
        VideoMessage::create(['video_id' => $video->getKey(), 'room_id' => $room->getKey()]);

        $messages = $room->messages()->childrenWith([
            TextMessage::class => ['images'],
            VideoMessage::class => ['video'],
        ])->get();

        $this->assertCount(2, $messages);
        $this->assertTrue($messages->whereInstanceOf(TextMessage::class)->every->relationLoaded('images'));
        $this->assertTrue($messages->whereInstanceOf(VideoMessage::class)->every->relationLoaded('video'));
    }

    /** @test */
    public function eager_load_children_count_from_relationship_query(): void
    {
        $room = Room::create(['name' => 'General']);

        $textMessage = TextMessage::create(['room_id' => $room->getKey()]);
        $textMessage->images()->create(['url' => 'https://example.com/image1.jpg']);

        $video = Video::create(['url' => 'https://example.com/video1.mp5']);
        VideoMessage::create(['video_id' => $video->getKey(), 'room_id' => $room->getKey()]);

        $messages = $room->messages()->childrenWithCount([
            TextMessage::class => ['images'],
        ])->get();

        $this->assertCount(2, $messages);
        $this->assertEquals(1, $messages->firstWhere(fn ($message) => $message instanceof TextMessage)->images_count);
        $this->assertNull($messages->firstWhere(fn ($message) => $message instanceof VideoMessage)->images_count);
    }
}
