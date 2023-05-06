<?php
include 'functions.php';

$items = getItemsWithHighestBids();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureAuction - Items</title>
</head>
<body>
    <h1>Items for Auction</h1>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Starting Price</th>
                <th>Highest Bid</th>
                <th>Place Bid</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['starting_price']); ?></td>
                    <td><?php echo htmlspecialchars($item['highest_bid'] ? $item['highest_bid'] : 'No bids yet'); ?></td>
                    <td>
                        <!-- Place Bid form -->
                        <form action="index.php" method="post">
                            <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                            <input type="number" name="bid_amount" step="0.01" min="<?php echo htmlspecialchars($item['highest_bid'] ? $item['highest_bid'] : $item['starting_price']); ?>" required>
                            <input type="submit" value="Place Bid">
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>
