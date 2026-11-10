<?php

namespace App\Services;

use App\Models\AdminUser;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class VerdanttApiClient
{
    protected function baseUrl(): string
    {
        return config('verdantt.api_base_url');
    }

    protected function request(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl())->acceptJson()->timeout(30);

        /** @var AdminUser|null $user */
        $user = Auth::guard('admin')->user();

        if ($user) {
            $request = $request->withToken($user->apiToken);
        }

        return $request;
    }

    public function login(string $email, string $password): Response
    {
        return Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->timeout(30)
            ->post('/api/auth/login', [
                'email' => $email,
                'password' => $password,
            ]);
    }

    public function get(string $path, array $query = []): Response
    {
        return $this->request()->get($path, $query);
    }

    public function post(string $path, array $data = []): Response
    {
        return $this->request()->post($path, $data);
    }

    public function put(string $path, array $data = []): Response
    {
        return $this->request()->put($path, $data);
    }

    public function patch(string $path, array $data = []): Response
    {
        return $this->request()->patch($path, $data);
    }

    public function delete(string $path): Response
    {
        return $this->request()->delete($path);
    }

    /**
     * @param  array<string, mixed>  $fields
     * @param  array<string, UploadedFile|null>  $files
     */
    public function postMultipart(string $path, array $fields = [], array $files = [], string $method = 'POST'): Response
    {
        // File uploads go through the API's image processing/storage, which can
        // easily exceed the default 30s on larger images.
        $request = $this->request()->connectTimeout(10)->timeout(120);

        foreach ($files as $name => $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $request = $request->attach($name, fopen($file->getRealPath(), 'r'), $file->getClientOriginalName());
            }
        }

        $request = $request->asMultipart();
        $body = $this->multipartFields($fields);

        return $method === 'PUT'
            ? $request->put($path, $body)
            : $request->post($path, $body);
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<int, array{name: string, contents: mixed}>
     */
    protected function multipartFields(array $fields): array
    {
        $result = [];

        foreach ($fields as $name => $value) {
            $result[] = [
                'name' => $name,
                'contents' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
            ];
        }

        return $result;
    }
}
