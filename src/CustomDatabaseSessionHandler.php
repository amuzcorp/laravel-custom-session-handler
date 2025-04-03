<?php

namespace Amuz\CustomSession;

use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Http\Request;

class CustomDatabaseSessionHandler extends DatabaseSessionHandler
{
    protected array $excludedRouteNames = [];
    protected array $excludedCallbacks = [];

    public function excludeRoutes(array $routeNames): void
    {
        $this->excludedRouteNames = $routeNames;
    }

    public function addExclusionCallback(callable $callback): void
    {
        $this->excludedCallbacks[] = $callback;
    }

    public function write($sessionId, $data): bool
    {
        $request = request();

        foreach ($this->excludedRouteNames as $name) {
            if ($request->routeIs($name)) {
                return $this->writeWithoutLastActivity($sessionId, $data);
            }
        }

        foreach ($this->excludedCallbacks as $callback) {
            if ($callback($request) === true) {
                return $this->writeWithoutLastActivity($sessionId, $data);
            }
        }

        return parent::write($sessionId, $data);
    }

    protected function writeWithoutLastActivity(string $sessionId, string $data): bool
    {
        return $this->getQuery()->updateOrInsert(
            ['id' => $sessionId],
            [
                'payload' => base64_encode($data),
                'user_id' => $this->getUserId(),
            ]
        );
    }

    protected function getUserId(): ?int
    {
        try {
            return optional(auth()->user())->getAuthIdentifier();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
