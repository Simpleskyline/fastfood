<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/db.php";
require_once __DIR__ . "/../../../config/auth_check.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$items = $data["items"] ?? [];

if (!is_array($items) || count($items) === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Cart is empty"]);
    exit;
}

$userId = $_SESSION["user_id"];

try {
    $pdo->beginTransaction();

    $total = 0;
    $validatedItems = [];

    // Validate food items and calculate total from DB (NOT frontend)
    foreach ($items as $item) {
        $foodId = (int)($item["food_id"] ?? 0);
        $qty = (int)($item["quantity"] ?? 0);

        if ($foodId <= 0 || $qty <= 0) {
            throw new Exception("Invalid cart item");
        }

        $stmt = $pdo->prepare(
            "SELECT id, price FROM food_items WHERE id = ? AND active = 1"
        );
        $stmt->execute([$foodId]);
        $food = $stmt->fetch();

        if (!$food) {
            throw new Exception("Food item not found: $foodId");
        }

        $lineTotal = $food["price"] * $qty;
        $total += $lineTotal;

        $validatedItems[] = [
            "food_id" => $foodId,
            "quantity" => $qty,
            "price" => $food["price"]
        ];
    }

    // Create order
    $stmt = $pdo->prepare(
        "INSERT INTO orders (user_id, total_amount, status)
         VALUES (?, ?, 'pending')"
    );
    $stmt->execute([$userId, $total]);

    $orderId = $pdo->lastInsertId();

    // Insert order items
    $stmt = $pdo->prepare(
        "INSERT INTO order_items (order_id, food_item_id, quantity, price)
         VALUES (?, ?, ?, ?)"
    );

    foreach ($validatedItems as $item) {
        $stmt->execute([
            $orderId,
            $item["food_id"],
            $item["quantity"],
            $item["price"]
        ]);
    }

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Order placed successfully",
        "order_id" => $orderId,
        "total" => $total
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        "error" => "Order failed",
        "details" => $e->getMessage()
    ]);
}
