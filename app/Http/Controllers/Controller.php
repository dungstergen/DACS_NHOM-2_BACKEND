<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="Room Rental API",
 *         description="API documentation"
 *     ),
 *     @OA\Server(
 *         url="http://127.0.0.1:8000",
 *         description="Local development"
 *     ),
 *     @OA\Tag(
 *         name="Authentication",
 *         description="Auth endpoints"
 *     )
 * )
 */
abstract class Controller
{
    //
}
