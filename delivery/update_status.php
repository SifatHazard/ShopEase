<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('delivery');

$agentId = $_SESSION['user_id'];
$orderId = (int)($_POST['order_id'] ?? 0);
$action = $_POST['action'] ?? '';


$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND delivery_agent_id = ?");
$stmt->bind_param("ii", $orderId, $agentId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    flash('error', 'Order not found or not assigned to you.');
    header('Location: /ecommerce/delivery/dashboard.php');
    exit;
}

switch ($action) {

    case 'accept':
        if ($order['delivery_status'] === 'assigned') {
            $upd = $conn->prepare("UPDATE orders SET delivery_status = 'accepted', delivery_note = NULL WHERE id = ?");
            $upd->bind_param("i", $orderId);
            $upd->execute();
            $upd->close();
            flash('success', "Order #$orderId accepted.");
        }
        break;

    case 'reject':
        if ($order['delivery_status'] === 'assigned') {
            
            $upd = $conn->prepare("UPDATE orders SET delivery_status = 'unassigned', delivery_agent_id = NULL, delivery_note = 'Rejected by previous agent' WHERE id = ?");
            $upd->bind_param("i", $orderId);
            $upd->execute();
            $upd->close();
            flash('success', "Order #$orderId rejected and sent back to admin for reassignment.");
        }
        break;

    case 'out_for_delivery':
        if ($order['delivery_status'] === 'accepted') {
            
            $otp = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $upd = $conn->prepare("UPDATE orders SET delivery_status = 'out_for_delivery', delivery_otp = ? WHERE id = ?");
            $upd->bind_param("si", $otp, $orderId);
            $upd->execute();
            $upd->close();
            flash('success', "Order #$orderId marked as out for delivery. OTP generated for handoff confirmation.");
        }
        break;

    case 'delivered':
        if ($order['delivery_status'] === 'out_for_delivery') {
            $enteredOtp = trim($_POST['otp'] ?? '');
            if ($enteredOtp === '' || $enteredOtp !== $order['delivery_otp']) {
                flash('error', 'Incorrect or missing OTP. Ask the customer for their delivery code.');
            } else {
                
                $conn->begin_transaction();
                try {
                    $upd = $conn->prepare("UPDATE orders SET delivery_status = 'delivered', order_status = 'confirmed' WHERE id = ?");
                    $upd->bind_param("i", $orderId);
                    $upd->execute();
                    $upd->close();

                    $updItems = $conn->prepare("UPDATE order_items SET item_status = 'delivered' WHERE order_id = ?");
                    $updItems->bind_param("i", $orderId);
                    $updItems->execute();
                    $updItems->close();

                    $conn->commit();
                    flash('success', "Order #$orderId marked as delivered. Great job!");
                } catch (Exception $e) {
                    $conn->rollback();
                    flash('error', 'Something went wrong updating the order. Please try again.');
                }
            }
        }
        break;

    case 'failed':
        if ($order['delivery_status'] === 'out_for_delivery') {
            $note = 'Delivery attempt failed - customer unreachable or refused';
            $upd = $conn->prepare("UPDATE orders SET delivery_status = 'failed', delivery_note = ? WHERE id = ?");
            $upd->bind_param("si", $note, $orderId);
            $upd->execute();
            $upd->close();
            flash('error', "Order #$orderId marked as failed delivery.");
        }
        break;
}

header('Location: /ecommerce/delivery/dashboard.php');
exit;
