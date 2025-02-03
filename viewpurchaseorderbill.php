<?php 
include("header.php");

// Function to sanitize input
function sanitizeInput($data) {
    global $con;
    return mysqli_real_escape_string($con, trim($data));
}

// Delete record handling
if(isset($_GET['deleteid'])) {
    $deleteid = sanitizeInput($_GET['deleteid']);
    $sql = "DELETE FROM purchase_request WHERE purchase_request_id='$deleteid'";
    if(!mysqli_query($con,$sql)) {
        echo "<script>alert('Failed to delete record'); </script>";
    } else {
        echo "<script>alert('This record deleted successfully..'); </script>";
    }
}

// Form submission handling
if(isset($_POST['submit'])) {
    $purchaserequestid = sanitizeInput($_GET['purchaserequestid']);
    $price = sanitizeInput($_POST['price']);
    
    // Check for existing pending purchase order
    $sqlpurchase_order = "SELECT * FROM purchase_order 
                         WHERE purchase_request_id='$purchaserequestid' 
                         AND status='Pending'";
    $qsqlpurchase_order = mysqli_query($con,$sqlpurchase_order);
    
    if(mysqli_num_rows($qsqlpurchase_order) >= 1) {    
        // Update existing order
        $sql = "UPDATE purchase_request SET status='Active' 
                WHERE purchase_request_id='$purchaserequestid'";
        $qsql = mysqli_query($con,$sql);
        
        $sqlupd = "UPDATE `purchase_order` 
                   SET `purchase_amt`='$price' 
                   WHERE purchase_request_id='$purchaserequestid'";
        if(!mysqli_query($con,$sqlupd)) {
            echo "Error in mysqli query";
        } else {
            echo "<script>alert('Purchase order has been updated successfully...');</script>";
        }
    } else {
        // Create new order
        $sql = "UPDATE purchase_request SET status='Active' 
                WHERE purchase_request_id='$purchaserequestid'";
        $qsql = mysqli_query($con,$sql);
        
        // Sanitize all POST data
        $product_id = sanitizeInput($_POST['product_id']);
        $customer_id = sanitizeInput($_POST['customer_id']);
        $request_date = sanitizeInput($_POST['request_date']);
        $request_time = sanitizeInput($_POST['request_time']);
        $quantity = sanitizeInput($_POST['quantity']);
        
        $sqlins = "INSERT INTO `purchase_order`
                  (`product_id`, `purchase_request_id`, `customer_id`, 
                   `seller_id`, `purchase_order_date`, `purchase_order_time`, 
                   `purchase_amt`, `quantity`, `status`) 
                  VALUES 
                  ('$product_id','$purchaserequestid','$customer_id',
                   '$_SESSION[sellerid]','$request_date','$request_time',
                   '$price','$quantity','Pending')";
        
        if(!mysqli_query($con,$sqlins)) {
            echo "Error in mysqli query";
        } else {
            echo "<script>alert('Your Purchase Order Sent Successfully...');</script>";    
            
            // Complex query to get all related information
            $sql_detailed = "SELECT 
                pr.purchase_request_id,
                pr.status as request_status,
                p.title as product_name,
                p.quantity_type,
                c.customer_name,
                c.mobile_no,
                s.seller_name,
                pr.quantity as requested_quantity,
                pr.request_date,
                pr.request_date_expire,
                pr.note,
                po.purchase_amt,
                po.status as order_status,
                (SELECT AVG(po2.purchase_amt) 
                 FROM purchase_order po2 
                 WHERE po2.product_id = p.product_id) as avg_product_price,
                (SELECT COUNT(*) 
                 FROM purchase_order po3 
                 WHERE po3.customer_id = pr.customer_id) as customer_total_orders,
                DATEDIFF(pr.request_date_expire, CURRENT_DATE) as days_until_expiry
            FROM 
                purchase_request pr
            INNER JOIN 
                product p ON p.product_id = pr.product_id
            INNER JOIN 
                customer c ON c.customer_id = pr.customer_id
            INNER JOIN 
                seller s ON s.seller_id = p.seller_id
            LEFT JOIN 
                purchase_order po ON po.purchase_request_id = pr.purchase_request_id
            WHERE 
                pr.purchase_request_id = '$purchaserequestid'";
            
            $result_detailed = mysqli_query($con, $sql_detailed);
            $order_details = mysqli_fetch_assoc($result_detailed);
            
            // Prepare SMS message
            $msg = "Your order for {$order_details['product_name']} has been placed successfully with the bill amount - Rs. $price. Kindly make the payment before {$_POST['expdate']}. Otherwise the order will be cancelled.";
            
            // Send SMS notification
            include("smsapi.php");
            if(isset($smsstatus) && $smsstatus == "Enabled") {
                $mobno = $order_details['mobile_no'];
                $msg = str_replace(" ", "%20", $msg);
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: 0'));
                curl_setopt($ch, CURLOPT_URL, "https://www.smsgatewayhub.com/api/mt/SendSMS?APIKey=" . $smsapi . "&senderid=" . $senderid . "&channel=2&DCS=0&flashsms=0&number=$mobno&text=$msg&route=21");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                
                $buffer = curl_exec($ch);
                curl_close($ch);
            }
        }
    }
}
?>

<main id="main">
    <!-- Cta Section -->
    <section id="cta" class="cta">
        <div class="container">
            <div class="text-center" data-aos="zoom-in">
                <br><br>
                <h3>Send Quotation</h3>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-12" data-aos="fade-up" data-aos-delay="100">
                    <div class="info mt-4">
                        <center><h4>Kindly enter your budget for this produce...</h4></center>
                        <hr>

                        <!-- Display Purchase Request Details -->
                        <table class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th><strong>Product</strong></th>
                                    <th><strong>Quantity</strong></th>
                                    <th><strong>Request Date</strong></th>
                                    <th><strong>Expiry Date</strong></th>
                                    <th><strong>Note</strong></th>
                                    <th><strong>Status</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Complex query to get purchase request details with analytics
                                $sql = "SELECT 
                                    pr.*,
                                    pr.quantity as purchase_request_quantity,
                                    pr.status as purchase_request_status,
                                    p.title,
                                    p.quantity_type,
                                    (SELECT AVG(po.purchase_amt) 
                                     FROM purchase_order po 
                                     WHERE po.product_id = p.product_id) as avg_price,
                                    (SELECT COUNT(*) 
                                     FROM purchase_order po 
                                     WHERE po.product_id = p.product_id) as total_orders,
                                    DATEDIFF(pr.request_date_expire, CURRENT_DATE) as days_remaining
                                FROM 
                                    purchase_request pr
                                INNER JOIN 
                                    product p ON p.product_id = pr.product_id
                                WHERE 
                                    p.seller_id = '$_SESSION[sellerid]'
                                    AND pr.purchase_request_id = '$_GET[purchaserequestid]'";
                                
                                $qsql = mysqli_query($con, $sql);
                                $rs = mysqli_fetch_array($qsql);
                                
                                echo "<tr>
                                    <td>{$rs['title']}</td>
                                    <td>{$rs['purchase_request_quantity']}</td>
                                    <td>{$rs['request_date']}</td>
                                    <td>{$rs['request_date_expire']}</td>
                                    <td>{$rs['note']}</td>
                                    <td>{$rs['purchase_request_status']}</td>
                                </tr>";
                                ?>
                            </tbody>
                        </table>
                        
                        <hr>
                        <h2>Sales Price</h2>
                        
                        <?php
                        // Get existing purchase order details
                        $sqlpurchase_order = "SELECT * FROM purchase_order 
                                            WHERE purchase_request_id='$_GET[purchaserequestid]'";
                        $qsqlpurchase_order = mysqli_query($con, $sqlpurchase_order);
                        $rspurchase_order = mysqli_fetch_array($qsqlpurchase_order);
                        ?>

                        <!-- Price Input Form -->
                        <form method="post" action="" name="frmpurchaseorderbill" onSubmit="return validatepurchaseorderbill()">
                            <input type="hidden" name="product_id" value="<?php echo $rs['product_id']; ?>">
                            <input type="hidden" name="purchase_request_id" value="<?php echo $rs['purchase_request_id']; ?>">
                            <input type="hidden" name="customer_id" value="<?php echo $rs['customer_id']; ?>">
                            <input type="hidden" name="quantity" value="<?php echo $rs['purchase_request_quantity']; ?>">
                            <input type="hidden" name="request_date" value="<?php echo date("Y-m-d"); ?>">
                            <input type="hidden" name="request_time" value="<?php echo date("h:i:s"); ?>">
                            <input type="hidden" name="expdate" value="<?php echo $rs["request_date_expire"]; ?>">

                            <table class="table table-striped table-bordered" style="width:100%">
                                <tbody>
                                    <tr>
                                        <td>Quantity: (In <?php echo $rs['quantity_type']; ?>)</td>
                                        <td>
                                            <input type="text" name="qty" id="qty" 
                                                   value="<?php echo $rs['purchase_request_quantity']; ?>" 
                                                   readonly class="form-control">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Price:</td>
                                        <td>
                                            <input type="text" name="price" id="price" 
                                                   value="<?php echo $rspurchase_order['purchase_amt']; ?>" 
                                                   class="form-control">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Average Market Price:</td>
                                        <td><?php echo number_format($rs['avg_price'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Total Orders for this Product:</td>
                                        <td><?php echo $rs['total_orders']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Days until Expiry:</td>
                                        <td><?php echo $rs['days_remaining']; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">&nbsp;</th>
                                        <td>
                                            <input type="submit" name="submit" id="submit" 
                                                   value="Submit" class="btn btn-success">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include("footer.php"); ?>

<script type="application/javascript">
function validatepurchaseorderbill() {
    var numericExpression = /^[0-9.]+$/;
    if(document.frmpurchaseorderbill.price.value == "") {
        alert("Kindly enter the price..");
        document.frmpurchaseorderbill.price.focus();
        return false;
    }            
    else if(!document.frmpurchaseorderbill.price.value.match(numericExpression)) {
        alert("Kindly enter only numbers.");
        document.frmpurchaseorderbill.price.focus();
        return false;
    }
    return true;
}

function delconfirm() {
    return confirm("Are you sure you want to delete this record?");
}
</script>