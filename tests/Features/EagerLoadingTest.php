<?php

namespace Parental\Tests\Features;

use Parental\Tests\Models\Room;
use Parental\Tests\Models\TextMessage;
use Parental\Tests\Models\Video;
use Parental\Tests\Models\VideoMessage;
use Parental\Tests\TestCase;

class EagerLoadingTest extends TestCase
{
    /** @test */
    public function eager_load_children_on_collection(): void
    {
        $room = Room::create(['name' => 'General']);

        $textMessage = TextMessage::create(['room_id' => $room->getKey()]);
        $textMessage->images()->create(['url' => 'https://example.com/image1.jpg']);

        $video = Video::create(['url' => 'https://example.com/video1.mp5']);
        VideoMessage::create(['room_id' => $room->getKey(), 'video_id' => $video->getKey()]);

        $messages = $room->messages;

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
        $room = Room::create(['name' => 'General']);

        $textMessage = TextMessage::create(['room_id' => $room->getKey()]);
        $textMessage->images()->create(['url' => 'https://example.com/image1.jpg']);

        $video = Video::create(['url' => 'https://example.com/video1.mp5']);
        VideoMessage::create(['room_id' => $room->getKey(), 'video_id' => $video->getKey()]);

        $messages = $room->messages;

        $messages->loadChildrenCount([
            TextMessage::class => ['images'],
        ]);

        $this->assertEquals($messages->firstWhere(fn ($message) => $message instanceof TextMessage)->images_count, 1);
    }

    /** @test */
    public function eager_load_children_on_paginator(): void
    {
        $room = Room::create(['name' => 'General']);

        $textMessage = TextMessage::create(['room_id' => $room->getKey()]);
        $textMessage->images()->create(['url' => 'https://example.com/image1.jpg']);

        $video = Video::create(['url' => 'https://example.com/video1.mp5']);
        VideoMessage::create(['room_id' => $room->getKey(), 'video_id' => $video->getKey()]);

        $messages = $room->messages()->paginate();

        $messages->loadChildrenCount([
            TextMessage::class => ['images'],
        ]);

        $this->assertEquals($messages->firstWhere(fn ($message) => $message instanceof TextMessage)->images_count, 1);
    }
}
