<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ConvertCase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge($this->convertArrayKeysToSnakeCase($request->all()));

        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $originalData = $response->getData(true); // Get response data as array
            $convertedData = $this->convertArrayKeysToCamelCase($originalData);
            $response->setData($convertedData); // Set the modified data back to the response
        }

        return $response;
    }

    private function convertArrayKeysToSnakeCase(array $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $snakeKey = Str::snake($key);
            if (is_array($value)) {
                $value = $this->convertArrayKeysToSnakeCase($value);
            }
            $result[$snakeKey] = $value;
        }
        return $result;
    }

    private function convertArrayKeysToCamelCase(array $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $camelKey = Str::camel($key);
            if (is_array($value)) {
                $value = $this->convertArrayKeysToCamelCase($value);
            }
            $result[$camelKey] = $value;
        }
        return $result;
    }
}
