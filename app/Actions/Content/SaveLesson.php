<?php

namespace App\Actions\Content;

use App\Enums\LessonType;
use App\Models\Lesson;
use App\Models\Module;
use App\Support\RichText;
use Illuminate\Validation\ValidationException;

class SaveLesson
{
    public function handle(Module $module, array $data, ?Lesson $lesson = null): Lesson
    {
        $type = LessonType::from($data['type']);

        $payload = [
            'title' => $data['title'],
            'type' => $type,
            'video_url' => null,
            'body' => null,
        ];

        if ($type === LessonType::Video) {
            $payload['video_url'] = trim($data['video_url']);
        } else {
            $body = RichText::clean($data['body'] ?? null);

            if ($body === null || trim(strip_tags($body)) === '') {
                throw ValidationException::withMessages(['body' => 'Write some content for this lesson.']);
            }

            $payload['body'] = $body;
        }

        if ($lesson) {
            $lesson->fill($payload)->save();

            return $lesson;
        }

        $lesson = new Lesson($payload);
        $lesson->module_id = $module->id;
        $lesson->position = $module->nextItemPosition();
        $lesson->save();

        return $lesson;
    }
}
