<?php 
include("header.php");

if(isset($_GET['deleteid']))
{
    $sql = "DELETE FROM purchase_request WHERE purchase_request_id='$_GET[deleteid]'";
    if(!mysqli_query($con,$sql))
    {
        echo "<script>alert('Failed to delete record'); </script>";
    }
    else
    {
        echo "<script>alert('This record deleted successfully..'); </script>";
    }
}
?>
  <main id="main">
    <section id="cta" class="cta">
      <div class="container">
        <div class="text-center" data-aos="zoom-in">
		<br><br>
          <h3>Farm Produce Purchase Order</h3>
        </div>
      </div>
    </section>

    <section id="contact" class="contact">
      <div class="container">
        <div class="row">
          <div class="col-lg-12" data-aos="fade-up" data-aos-delay="100">
            <div class="info mt-4">
              <?php
              $sql = "WITH OrderAnalysis AS (
                  SELECT 
                      po.purchase_order_id,
                      po.customer_id,
                      po.product_id,
                      po.purchase_order_date,
                      po.status,
                      po.purchase_amt,
                      po.quantity,
                      p.title,
                      p.quantity_type,
                      c.customer_name,
                      DATEDIFF(DATE_ADD(po.purchase_order_date, INTERVAL 7 DAY), CURRENT_DATE()) as days_remaining,
                      CASE 
                          WHEN CURRENT_DATE() > DATE_ADD(po.purchase_order_date, INTERVAL 7 DAY) AND po.status = 'Pending' 
                          THEN 'Order cancelled'
                          ELSE po.status 
                      END as calculated_status,
                      ROW_NUMBER() OVER (PARTITION BY po.customer_id ORDER BY po.purchase_order_date DESC) as order_rank
                  FROM purchase_order po
                  INNER JOIN product p ON po.product_id = p.product_id
                  INNER JOIN customer c ON po.customer_id = c.customer_id
                  WHERE po.customer_id = '$_SESSION[customerid]'
              )
              SELECT * FROM OrderAnalysis";

              $qsql = mysqli_query($con,$sql);
              if(mysqli_num_rows($qsql) == 0)
              {
                  echo "<center>There is no Purchase Order to display!!</center>";
              }
              else
              {
              ?>
                <table ID="datatable" class="table table-striped table-bordered" style="width:100%">
                  <THEAD>
                    <tr>
                      <th height="42"><strong>Product</strong></th>
                      <th><strong>Customer Name</strong></th>
                      <th><strong>Quotation Date</strong></th>
                      <th><strong>Make Payment Before</strong></th>
                      <th><strong>Amount</strong></th>
                      <th><strong>Quantity</strong></th>
                      <th><strong>Status</strong></th>
                      <th><strong>Action</strong></th>
                    </tr>
                  </THEAD>
                  <TBODY>
                    <?php
                    while($rs = mysqli_fetch_array($qsql))
                    {
                        $dexdate = date('Y-m-d', strtotime($rs['purchase_order_date']. ' + 7 day'));
                        $dt = date("Y-m-d");
                        $date1 = new DateTime($dt);
                        $date2 = new DateTime($dexdate);
                        
                        echo "<tr>
                          <td>&nbsp;$rs[title]</td>
                          <td>&nbsp;$rs[customer_name]</td>
                          <td>&nbsp;" . date("d-m-Y",strtotime($rs['purchase_order_date'])) . "</td>
                          <td>" . date("d-m-Y",strtotime($dexdate)) . "</td>
                          <td>&nbsp;" . $config['CURRENCY_SYMBOL'] . $rs['purchase_amt'] . "</td>
                          <td>&nbsp;$rs[quantity] $rs[quantity_type]</td>
                          <td>&nbsp;$rs[calculated_status]</td>";
                          
                        if($rs['status'] == "Pending")
                        {
                            if ($date1 > $date2)
                            {
                                echo "<td>Expired</td>";
                            }
                            else
                            {
                                echo "<td><a href='purchaseorderpayment.php?purchaseorderid=$rs[purchase_order_id]' class='btn btn-info'>Make Payment - 1</a></td>";
                                echo "<td><a href='sslpayment.php?price=$rs[purchase_amt]' class='btn btn-info'>Make Payment - 2</a></td>";
                            }
                        }
                        else
                        {
                            echo "<td>&nbsp;</td>";	  
                        }
                        echo "</tr>";
                    }
                    ?>
                  </TBODY>
                </table>
              <?php
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  
<?php
include("footer.php");
?>
<script type="application/javascript">
function delconfirm()
{
    if(confirm("Are you sure you want to delete this record?") == true)
    {
        return true;
    }
    else
    {
        return false;
    }
}
</script>