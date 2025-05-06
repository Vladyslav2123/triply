<?php

namespace App\OpenApi\Schemas\Common;

/**
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     title="Pagination Links",
 *     description="Navigation links for paginated responses",
 *
 *     @OA\Property(
 *         property="first",
 *         type="string",
 *         format="url",
 *         example="https://api.triply.test/api/v1/listings?page=1",
 *         description="URL for the first page"
 *     ),
 *     @OA\Property(
 *         property="last",
 *         type="string",
 *         format="url",
 *         example="https://api.triply.test/api/v1/listings?page=5",
 *         description="URL for the last page"
 *     ),
 *     @OA\Property(
 *         property="prev",
 *         type="string",
 *         format="url",
 *         nullable=true,
 *         example=null,
 *         description="URL for the previous page (null if on first page)"
 *     ),
 *     @OA\Property(
 *         property="next",
 *         type="string",
 *         format="url",
 *         nullable=true,
 *         example="https://api.triply.test/api/v1/listings?page=2",
 *         description="URL for the next page (null if on last page)"
 *     )
 * )
 */
class PaginationLinksSchema {}
