<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\SecurityScheme(
 *     securityScheme="cookieAuth",
 *     type="apiKey",
 *     in="cookie",
 *     name="laravel_session"
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="full_name", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="role", type="string"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Amenity",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string")
 * )
 *
 * @OA\Schema(
 *     schema="RoomImage",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="room_id", type="integer"),
 *     @OA\Property(property="image_url", type="string"),
 *     @OA\Property(property="sort_order", type="integer")
 * )
 *
 * @OA\Schema(
 *     schema="Room",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="address", type="string"),
 *     @OA\Property(property="district", type="string"),
 *     @OA\Property(property="city", type="string"),
 *     @OA\Property(property="price_monthly", type="integer"),
 *     @OA\Property(property="deposit_amount", type="integer"),
 *     @OA\Property(property="area_sqm", type="number", format="float"),
 *     @OA\Property(property="max_occupants", type="integer"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="created_by", type="integer"),
 *     @OA\Property(property="amenities", type="array", @OA\Items(ref="#/components/schemas/Amenity")),
 *     @OA\Property(property="images", type="array", @OA\Items(ref="#/components/schemas/RoomImage")),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Post",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="content", type="string"),
 *     @OA\Property(property="thumbnail_url", type="string", nullable=true),
 *     @OA\Property(property="author_id", type="integer"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="author", ref="#/components/schemas/User")
 * )
 *
 * @OA\Schema(
 *     schema="Appointment",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="room_id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="scheduled_at", type="string", format="date-time"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="note", type="string", nullable=true),
 *     @OA\Property(property="room", ref="#/components/schemas/Room"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Payment",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="order_id", type="integer"),
 *     @OA\Property(property="amount", type="integer"),
 *     @OA\Property(property="payment_method", type="string"),
 *     @OA\Property(property="transaction_id", type="string"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="room_id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="amount", type="integer"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="payment_method", type="string"),
 *     @OA\Property(property="payment_ref", type="string", nullable=true),
 *     @OA\Property(property="room", ref="#/components/schemas/Room"),
 *     @OA\Property(property="payments", type="array", @OA\Items(ref="#/components/schemas/Payment")),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="RentalContract",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="room_id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="start_date", type="string", format="date"),
 *     @OA\Property(property="end_date", type="string", format="date"),
 *     @OA\Property(property="monthly_rent", type="integer"),
 *     @OA\Property(property="deposit_amount", type="integer"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="room", ref="#/components/schemas/Room"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="MonthlyBill",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="contract_id", type="integer"),
 *     @OA\Property(property="billing_month", type="string"),
 *     @OA\Property(property="room_rent", type="integer"),
 *     @OA\Property(property="electricity_old", type="integer"),
 *     @OA\Property(property="electricity_new", type="integer"),
 *     @OA\Property(property="electricity_cost", type="integer"),
 *     @OA\Property(property="water_old", type="integer"),
 *     @OA\Property(property="water_new", type="integer"),
 *     @OA\Property(property="water_cost", type="integer"),
 *     @OA\Property(property="internet_cost", type="integer"),
 *     @OA\Property(property="trash_cost", type="integer"),
 *     @OA\Property(property="parking_cost", type="integer"),
 *     @OA\Property(property="total_amount", type="integer"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="contract", ref="#/components/schemas/RentalContract"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="BillingConfig",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="electricity_price", type="integer"),
 *     @OA\Property(property="water_price", type="integer"),
 *     @OA\Property(property="internet_price", type="integer"),
 *     @OA\Property(property="trash_price", type="integer"),
 *     @OA\Property(property="parking_price", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class OpenApi
{
}
