<?php

namespace Parental\Tests\Features;

use Parental\Tests\Models\Message;
use Parental\Tests\Models\TextMessage;
use Parental\Tests\Models\Video;
use Parental\Tests\Models\VideoMessage;
use Parental\Tests\TestCase;
use RuntimeException;

class EagerLoadingOnOlderLaravelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (version_compare(app()->version(), '11.0.0', '>=')) {
            $this->markTestSkipped('Eager loading macros should work on Laravel 11 and above, so skipping these tests.');
        }
    }

    public static function eagerLoadingScenarios(): array
    {
        return [
            'eager loading children on model' => [
                function () {
                    $text = TextMessage::create();
                    $text->images()->create(['url' => 'https://example.com/image1.jpg']);

                    $text->loadChildren([
                        TextMessage::class => ['images'],
                        VideoMessage::class => ['video'],
                    ]);
                },
            ],
            'eager loading children count on model' => [
                function () {
                    $text = TextMessage::create();
                    $text->images()->create(['url' => 'https://example.com/image1.jpg']);

                    $text->loadChildrenCount([
                        TextMessage::class => ['images'],
                        VideoMessage::class => ['video'],
                    ]);
                },
            ],
            'eager load children on collection' => [
                function () {
                    $text = TextMessage::create();
                    $text->images()->create(['url' => 'https://example.com/image1.jpg']);

                    $video = Video::create(['url' => 'https://example.com/video1.mp4']);
                    VideoMessage::create(['video_id' => $video->getKey()]);

                    Message::all()->loadChildren([
                        TextMessage::class => ['images'],
                        VideoMessage::class => ['video'],
                    ]);
                },
            ],
            'eager load children count on collection' => [
                function () {
                    $text = TextMessage::create();
                    $text->images()->create(['url' => 'https://example.com/image1.jpg']);

                    $video = Video::create(['url' => 'https://example.com/video1.mp4']);
                    VideoMessage::create(['video_id' => $video->getKey()]);

                    Message::all()->loadChildrenCount([
                        TextMessage::class => ['images'],
                        VideoMessage::class => ['video'],
                    ]);
                },
            ],
            'eager load children on paginator' => [
                function () {
                    $text = TextMessage::create();
                    $text->images()->create(['url' => 'https://example.com/image1.jpg']);

                    $video = Video::create(['url' => 'https://example.com/video1.mp4']);
                    VideoMessage::create(['video_id' => $video->getKey()]);

                    Message::paginate()->loadChildren([
                        TextMessage::class => ['images'],
                        VideoMessage::class => ['video'],
                    ]);
                },
            ],
            'eager load children count on paginator' => [
                function () {
                    $text = TextMessage::create();
                    $text->images()->create(['url' => 'https://example.com/image1.jpg']);

                    $video = Video::create(['url' => 'https://example.com/video1.mp4']);
                    VideoMessage::create(['video_id' => $video->getKey()]);

                    Message::paginate()->loadChildrenCount([
                        TextMessage::class => ['images'],
                        VideoMessage::class => ['video'],
                    ]);
                },
            ],
            'eager loading children on query builder' => [
                function () {
                    $text = TextMessage::create();
                    $text->images()->create(['url' => 'https://example.com/image1.jpg']);

                    $video = Video::create(['url' => 'https://example.com/video1.mp4']);
                    VideoMessage::create(['video_id' => $video->getKey()]);

                    Message::query()->childrenWith([
                        TextMessage::class => ['images'],
                        VideoMessage::class => ['video'],
                    ])->get();
                },
            ],
            'eager loading children count on query builder' => [
                function () {
                    $text = TextMessage::create();
                    $text->images()->create(['url' => 'https://example.com/image1.jpg']);

                    $video = Video::create(['url' => 'https://example.com/video1.mp4']);
                    VideoMessage::create(['video_id' => $video->getKey()]);

                    Message::query()->childrenWithCount([
                        TextMessage::class => ['images'],
                        VideoMessage::class => ['video'],
                    ])->get();
                },
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider eagerLoadingScenarios
     */
    public function throws_exception_when_eager_loading_on_older_laravel_versions($scenario): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Eager loading on Parental models are only available in Laravel 11 and above.');

        $scenario();
    }
}
