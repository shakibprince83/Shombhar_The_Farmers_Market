<?php
include("header.php");
?>
  <main id="main">
    <section id="cta" class="cta">
      <div class="container">
        <div class="text-center" data-aos="zoom-in">
		<br><br><br>
          <h3>Bill Receipt</h3>
        </div>
      </div>
    </section>

    <section id="contact" class="contact">
      <div class="container">
        <div class="row">
          <div class="col-lg-12" data-aos="fade-up" data-aos-delay="100">
            <div class="info mt-4">
              <div class="logo mr-auto">
                <center><h1 class="text-light"><a href="index.php"><span><?php echo $config['PROJECT_TITLE']; ?></span></a></h1></center>
              </div>
            </div>
            <div class="row">
              <?php
              $sql = "SELECT 
                        bill.*, 
                        po.*, 
                        prod.title, 
                        prod.img_1, 
                        prod.quantity_type,
                        cust.customer_name, 
                        cust.address, 
                        cust.contact_no, 
                        cust.pincode as cust_pincode,
                        custcity.city as custcity, 
                        custstate.state as custstate, 
                        custcountry.country as custcountry,
                        sell.seller_name, 
                        sell.seller_address, 
                        sell.mobile_no, 
                        sell.pincode as sell_pincode,
                        sell.bank_name, 
                        sell.bank_acno, 
                        sell.bank_branch, 
                        sell.bank_IFSC,
                        selcity.city as selcity, 
                        selstate.state as selstate, 
                        selcountry.country as selcountry
                    FROM purchase_order_bill bill
                    JOIN purchase_order po ON bill.purchase_order_id = po.purchase_order_id
                    JOIN product prod ON po.product_id = prod.product_id
                    JOIN customer cust ON po.customer_id = cust.customer_id
                    JOIN seller sell ON po.seller_id = sell.seller_id
                    LEFT JOIN city custcity ON cust.city_id = custcity.city_id
                    LEFT JOIN state custstate ON cust.state_id = custstate.state_id
                    LEFT JOIN country custcountry ON cust.country_id = custcountry.country_id
                    LEFT JOIN city selcity ON sell.city_id = selcity.city_id
                    LEFT JOIN state selstate ON sell.state_id = selstate.state_id
                    LEFT JOIN country selcountry ON sell.country_id = selcountry.country_id
                    WHERE bill.purchase_order_bill_id = '$_GET[purchase_order_bill_id]'";
              
              $qsqlbill = mysqli_query($con,$sql);
              $rs = mysqli_fetch_array($qsqlbill);
              ?>
              <div class="col-md-4">
                <div class="info w-100 mt-4">
                  <h4 style="padding: 0 0 0 0px;">Payment Bill:</h4>
                  <p style="padding: 0 0 0 0px;"><b>Order Bill Number:</b> <?php echo $rs['purchase_order_bill_id']; ?></p>
                  <p style="padding: 0 0 0 0px;"><b>Paid Date:</b> <?php echo $rs['paid_date']; ?></p>
                  <p style="padding: 0 0 0 0px;"><b>Customer Name:</b> <?php echo $rs['customer_name']; ?></p>
                  <p style="padding: 0 0 0 0px;"><b>Seller Name:</b> <?php echo $rs['seller_name']; ?></p>
                </div>
              </div>
              <div class="col-md-4 mt-4">
                <div class="info">
                  <i class="icofont-envelope"></i>
                  <h4>Customer address:</h4>
                  <p><?php echo $rs['address']; ?><br>
                     <?php echo $rs['custcity']; ?> <br>
                     <?php echo $rs['custstate']; ?> <br>
                     <?php echo $rs['custcountry']; ?> <br>
                     PIN Code:<?php echo $rs['cust_pincode']; ?><br>
                     Ph. No. <?php echo $rs['contact_no']; ?>
                  </p>
                </div>
              </div>
              <div class="col-md-4 mt-4">
                <div class="info">
                  <i class="icofont-envelope"></i>
                  <h4>Seller Address:</h4>
                  <p><?php echo $rs['seller_address']; ?> <br>
                     <?php echo $rs['selcity']; ?> <br>
                     <?php echo $rs['selstate']; ?> <br>
                     <?php echo $rs['selcountry']; ?> <br>
                     PIN Code: <?php echo $rs['sell_pincode']; ?><br>
                     Ph. No. <?php echo $rs['mobile_no']; ?></p>
                </div>
              </div>
            </div>

            <form action="forms/contact.php" method="post" role="form" class="php-email-form mt-4">
              <div class="form-row">
                <div class="col-md-12 form-group">
                  <h2>Product Details</h2>
                  <table width="755" border="0" class="table table-bordered">
                    <tbody>
                      <tr>
                        <th><strong>Image</strong></th>
                        <th><strong>Product Name</strong></th>
                        <th><strong>Quantity</strong></th>
                        <th><strong>Total</strong></th>
                      </tr>
                      <tr>
                        <td>&nbsp;<img src='imgproduct/<?php echo $rs['img_1']; ?>' width='25' height='25'></td>
                        <td>&nbsp;<?php echo $rs['title']; ?></td>
                        <td>&nbsp;<?php echo $rs['quantity']; ?>&nbsp;<?php echo $rs['quantity_type']; ?></td>
                        <td>&nbsp;<span id='calccost1'><?php echo $config['CURRENCY_SYMBOL']; ?><?php echo $rs['purchase_amt']; ?></span></td>
                      </tr>
                      <tr>
                        <th height="33" scope="row">&nbsp;</th>
                        <th>&nbsp;</th>
                        <th><strong>Grand total</strong></th>
                        <th>&nbsp;<?php echo $config['CURRENCY_SYMBOL']; ?> <?php echo $rs['purchase_amt']; ?></th>
                      </tr>
                    </tbody>
                  </table>
                  <hr>
                  <table width="755" border="0" class="table table-bordered">
                    <tbody>
                      <tr>
                        <th width="231" height="31" scope="row" align="right"><strong>Payment type:</strong></th>
                        <th width="514" height="31" scope="row" align="left">&nbsp;<?php echo $rs['payment_type']; ?></th>
                      </tr>
                      <tr>
                        <th height="33" scope="row" align="right">&nbsp;<strong>Paid Date:</strong></th>
                        <th align="left">&nbsp;<?php echo $rs['paid_date']; ?></th>
                      </tr>
                      <tr>
                        <th height="33" scope="row" align="right">&nbsp;<strong>Seller Bank Account detail:</strong></th>
                        <th align="left">
                          <?php
                          echo "<strong>&nbsp;Bank Name: </strong>" . $rs['bank_name'] . "<br>";
                          echo "<strong>&nbsp;Bank Account number: </strong>" . $rs['bank_acno'] . "<br>";
                          echo "<strong>&nbsp;Branch: </strong>" . $rs['bank_branch'] . "<br>";
                          echo "<strong>&nbsp;IFSC Code: </strong>" . $rs['bank_IFSC'] . "<br>";
                          ?>
                        </th>
                      </tr>
                    </tbody>
                  </table>
                  <hr>
                  <center><button onclick="myFunction()" autofocus class="btn btn-primary">Print Bill</button></center>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </main>
  
<?php
include("footer.php");
?>
<script>
function myFunction() {
    window.print();
}
</script>