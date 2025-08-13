<?php

namespace Filaforge\HelloWidget\Filament\Widgets;

use Filament\Widgets\Widget;
use Filaforge\HelloWidget\Models\ChatMessage;

class HelloWidget extends Widget
{
    protected string $view = 'hello-widget::widget';

    protected int | string | array $columnSpan = 'full';


    public function getHeading(): string
    {
        return trans('hello-widget::widget.title');
    }

    /** @return array<int, array{user:string,message:string,created_at:string}> */
    public function getMessages(): array
    {
        $currentUserId = auth()->id();

        return ChatMessage::query()
            ->with(['user:id,name,email'])
            ->latest('id')
            ->take(50)
            ->get()
            ->sortBy('id')
            ->map(function (ChatMessage $m) use ($currentUserId) {
                $name = optional($m->user)->name;
                $email = optional($m->user)->email;
                $display = $name ?: ($email ?: 'User #'.$m->user_id);

                return [
                    'user' => $display,
                    'user_id' => $m->user_id,
                    'is_mine' => $currentUserId !== null && $m->user_id === $currentUserId,
                    'initials' => $this->makeInitials($name, $email),
                    'color' => $this->colorClass($m->user_id ?? 0, $name ?? $email ?? ''),
                    'message' => $m->message,
                    'created_at' => $m->created_at?->diffForHumans() ?? '',
                ];
            })
            ->values()
            ->all();
    }

    private function makeInitials(?string $name, ?string $email): string
    {
        $source = trim((string) ($name ?: ($email ?: 'U')));
        if ($source === '') {
            return 'U';
        }

        // Try to take first letters of up to two words
        $parts = preg_split('/\s+/', $source) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $p) {
            $initials .= mb_strtoupper(mb_substr($p, 0, 1));
        }
        if ($initials !== '') {
            return mb_substr($initials, 0, 2);
        }

        // Fallback: first two characters
        return mb_strtoupper(mb_substr($source, 0, 2));
    }

    private function colorClass(int $id, string $seed): string
    {
        $palette = [
            'bg-blue-500', 'bg-emerald-500', 'bg-rose-500', 'bg-amber-500', 'bg-fuchsia-500',
            'bg-sky-500', 'bg-teal-500', 'bg-indigo-500', 'bg-cyan-500', 'bg-violet-500',
        ];

        $hash = $id ?: (int) (abs(crc32($seed)));
        $index = $hash % count($palette);
        return $palette[$index];
    }

    public string $newMessage = '';

    public function sendMessage(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }
        $msg = trim($this->newMessage);
        if ($msg === '') {
            return;
        }
        ChatMessage::create([
            'user_id' => $user->getKey(),
            'message' => mb_substr($msg, 0, 2000),
        ]);
    $this->newMessage = '';
    $this->dispatch('publicChatMessageSent');
    }
}
