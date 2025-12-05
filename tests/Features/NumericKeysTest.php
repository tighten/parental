<?php

namespace Parental\Tests\Features;

use Parental\Tests\Models\Message;
use Parental\Tests\Models\TextMessage;
use Parental\Tests\Models\VideoMessage;
use Parental\Tests\TestCase;

class NumericKeysTest extends TestCase
{
    /** @test */
    public function child_type_keys_can_be_numeric(): void
    {
        $textMessage = Message::create(['type' => 1]);
        $videoMessage = Message::create(['type' => 2]);

        $this->assertInstanceOf(TextMessage::class, $textMessage);
        $this->assertInstanceOf(VideoMessage::class, $videoMessage);
    }

    /** @test */
    public function can_become_when_using_numeric_keys(): void
    {
        $textMessage = Message::create(['type' => 1]);

        $videoMessage = $textMessage->become(VideoMessage::class);
        $videoMessage->save();

        $this->assertInstanceOf(VideoMessage::class, $videoMessage);
        $this->assertEquals($textMessage->id, $videoMessage->id);
        $this->assertSame(2, $videoMessage->type);
    }
}
