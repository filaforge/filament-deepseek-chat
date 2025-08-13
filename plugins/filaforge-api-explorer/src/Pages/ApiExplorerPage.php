<?php

namespace Filaforge\ApiExplorer\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiExplorerPage extends Page implements \Filament\Forms\Contracts\HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';
    protected string $view = 'api-explorer::pages.api-explorer';
    protected static ?string $title = 'API Explorer';
    protected static ?string $navigationLabel = 'API Explorer';
    protected static string | \UnitEnum | null $navigationGroup = 'System';

    public ?array $data = [];
    public string $response = '';
    public int $statusCode = 0;
    public float $responseTime = 0;
    public array $requestHistory = [];

    public function mount(): void
    {
        $this->form->fill([
            'method' => 'GET',
            'url' => '',
            'headers' => '',
            'body' => ''
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('method')
                    ->label('HTTP Method')
                    ->options([
                        'GET' => 'GET',
                        'POST' => 'POST',
                        'PUT' => 'PUT',
                        'DELETE' => 'DELETE',
                        'PATCH' => 'PATCH',
                        'HEAD' => 'HEAD',
                        'OPTIONS' => 'OPTIONS',
                    ])
                    ->required()
                    ->default('GET'),

                TextInput::make('url')
                    ->label('URL')
                    ->placeholder('https://api.example.com/endpoint')
                    ->required()
                    ->url(),

                Textarea::make('headers')
                    ->label('Headers (JSON format)')
                    ->placeholder('{"Content-Type": "application/json", "Authorization": "Bearer token"}')
                    ->rows(3),

                Textarea::make('body')
                    ->label('Request Body')
                    ->placeholder('{"key": "value"}')
                    ->rows(5),
            ])
            ->statePath('data');
    }

    public function sendRequest(): void
    {
        $data = $this->form->getState();

        if (!$data['url']) {
            Notification::make()
                ->title('URL is required')
                ->danger()
                ->send();
            return;
        }

        try {
            $client = new Client([
                'timeout' => config('api-explorer.default_timeout', 30),
                'verify' => false,
            ]);

            $headers = [];
            if (!empty($data['headers'])) {
                $headers = $this->parseHeaders($data['headers']);
            }

            $options = [
                'headers' => $headers,
            ];

            if (!empty($data['body']) && in_array($data['method'], ['POST', 'PUT', 'PATCH'])) {
                $options['body'] = $data['body'];
            }

            $startTime = microtime(true);
            $response = $client->request($data['method'], $data['url'], $options);
            $endTime = microtime(true);

            $this->statusCode = $response->getStatusCode();
            $this->responseTime = round(($endTime - $startTime) * 1000, 2);
            $this->response = $this->formatResponse($response);

            // Add to history
            $this->requestHistory[] = [
                'method' => $data['method'],
                'url' => $data['url'],
                'status' => $this->statusCode,
                'time' => $this->responseTime,
                'timestamp' => now()->format('H:i:s'),
            ];

            Notification::make()
                ->title('Request completed')
                ->success()
                ->send();

        } catch (RequestException $e) {
            $this->statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $this->response = $e->getMessage();
            $this->responseTime = 0;

            Notification::make()
                ->title('Request failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Exception $e) {
            $this->response = 'Error: ' . $e->getMessage();
            $this->statusCode = 0;
            $this->responseTime = 0;

            Notification::make()
                ->title('Request failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function parseHeaders(string $headersString): array
    {
        try {
            return json_decode($headersString, true) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function formatResponse($response): string
    {
        $body = $response->getBody()->getContents();

        // Try to format JSON
        $decoded = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return $body;
    }

    public function clearResponse(): void
    {
        $this->response = '';
        $this->statusCode = 0;
        $this->responseTime = 0;
    }

    public function clearHistory(): void
    {
        $this->requestHistory = [];
    }

    public function loadSampleRequest(): void
    {
        $this->form->fill([
            'method' => 'GET',
            'url' => 'https://jsonplaceholder.typicode.com/posts/1',
            'headers' => '{"Content-Type": "application/json"}',
            'body' => ''
        ]);
    }
}
