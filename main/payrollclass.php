<?php 
//ini_set('display_errors', 1);
include('header.php');
ini_set('display_errors', 1);
//Run Payroll
$period=$_REQUEST['period'];
if ($_GET['action']=="postbenefits") {
//check if run already



		for($i=0;$i<count($_POST['lunch']);$i++)
			{
				$id=$_POST['id'][$i];
				$lunch = $_POST['lunch'][$i];
				$overtime= $_POST['overtime'][$i];
				$allowance= $_POST['allowance'][$i];
				$commission= $_POST['commission'][$i];
				$benefits= $_POST['ben'][$i];
		
					$staff=mysql_fetch_array(mysql_query("SELECT * FROM staff WHERE id='$id'"));
					$sname=$staff['staff_name'];
					$sal=$staff['salary'];
					$pno=$staff['payrollno'];
//payroll runs table
$result=mysql_query("SELECT * from payrollruns WHERE period='$period'");
$num=mysql_num_rows($result);
if ($result=0) {
	mysql_query("INSERT INTO payrollruns (period)values('$period')");
}elseif ($result>0) {

	mysql_query("UPDATE payrollruns set period='$period' where period='$period'");
}


mysql_query("INSERT INTO payroll_tbl(payrollrun,staffid,payrollno,sname,salary,lunch,allowance,commission,overtime,totalbenefits)values('$period','$id','$pno','$sname','$sal','$lunch','$allowance','$commission','$overtime','$benefits')");

					
				//echo "<script>alert('Success')</script>";
			}
			echo "<script>alert('Success Benefits Posted')</script>";
				echo "<script>location.replace('payroll.php')</script>";
}elseif ($_GET['action']=="postdeductions") {
	for($i=0;$i<count($_POST['deds']);$i++)
			{
				$id=$_POST['id'][$i];
				$deds= $_POST['deds'][$i];
				$nhif= $_POST['nhif'][$i];
				$nssf= $_POST['nssf'][$i];
				$advance= $_POST['advance'][$i];
				$tot= $_POST['tot'][$i];
				$date=date('Y-m-d');
				//cal paye and taxable income
				//RATES
				/*
					Up to 10,164	Up to 121,968	10
					10,165 - 19,740	121,969 - 236,880	15
					19,741 - 29,316	236,881 - 351,792	20
					29,317 - 38,892	351,793 - 466,704	25
					Above 38,892	Above 466,704	30

					taxableincome = (sal)
				*/	
					$staff=mysql_fetch_array(mysql_query("SELECT * FROM staff WHERE id='$id'"));
					$sname=$staff['staff_name'];
					$sal=$staff['salary'];
					$pno=$staff['payrollno'];
					$rates=mysql_fetch_array(mysql_query("SELECT totalbenefits FROM payroll_tbl WHERE staffid='$id'"));
					$bens=$rates['totalbenefits'];
					//$paye1=$paye2=$paye3=$paye4=$paye5=0;
					if ($sal<=13000) {

						$taxableincome=($sal+$bens)-$nssf;
					}elseif ($sal>13000) {
					
				$taxableincome=($sal+$bens)-$nssf;	
					}

				if ($taxableincome<=11180) {
				$paye1=round($taxableincome*.10,2 );
				$paye2=$paye3=$paye4=$paye5=0;
				$paye=($paye1+$paye2+$paye3+$paye4+$paye5)-1280;
				if($paye<0){
					$paye=0;
				}elseif($paye>=0){
					$paye=$paye;
				}

				}elseif ($taxableincome>11180 and $taxableincome<=21714) {
				$paye1=11180*.10;
				$t2=$taxableincome-11180;
				$paye2= round($t2*.15,2);
				$paye3=$paye4=$paye5=0;
				$paye=($paye1+$paye2+$paye3+$paye4+$paye5)-1280;

				}elseif ($taxableincome>21714 and $taxableincome<=32248) {
				$paye1=11180*.10;
				$paye2=round((21714-11180)*.15,2);
				$t3=$taxableincome-21714;
				$paye3= round($t3*.20,2);
				$paye4=$paye5=0;
				$paye=($paye1+$paye2+$paye3+$paye4+$paye5)-1280;

				}elseif ($taxableincome>32248 and $taxableincome<=42782) {
				$paye1=11180*.10;
				$paye2=round((21714-11180)*.15,2);
				$paye3=round((32248-21714)*.20,2);
				$t4=$taxableincome-32248;
				$paye4= round($t4*.25,2);
				$paye5=0;
				$paye=($paye1+$paye2+$paye3+$paye4+$paye5)-1280;
				
				}elseif ($taxableincome>42782) {
				$paye1=11180*.10;
				$paye2=round((21714-11180)*.15,2);
				$paye3=round((32248-21714)*.20,2);
				$paye4=round((42782-32248)*.25,2);
				$t5=$taxableincome-42782;
				$paye5= round($t5*.30,2);
				$paye=($paye1+$paye2+$paye3+$paye4+$paye5)-1280;
				}
				//net
				//$net=($taxableincome+$bens)-$paye-$tot;
				$net1=($sal+$bens)-($paye+$tot);
				$net=round($net1,0);
				//echo "$sname: $net <br>";
				mysql_query(" UPDATE  payroll_tbl SET nhif='$nhif',nssf='$nssf',advance='$advance',totaldeductions='$tot',deduction='$deds',taxableincome='$taxableincome',tax='$paye',netpay='$net',daterun='$date' WHERE staffid='$id' and payrollrun='$period' ");
				
				
			}
				echo "<script>alert('Success Deductions Posted')</script>";
				echo "<script>location.replace('payroll.php')</script>";
}
elseif($_GET['action']=="confirmpayroll"){
	$query="UPDATE payroll_tbl SET status='1' WHERE  payrollrun='$period' and status='0'";
	mysql_query($query);
	echo "<script>location.replace('confirmedpayroll.php?period=$period')</script>";
}

?>