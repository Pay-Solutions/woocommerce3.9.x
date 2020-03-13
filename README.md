# woocommerce3.9.x

<b>คู่มือการตั้งค่า Paysolutions Payment Gateway สำหรับ Woocommerce v3.8/3.9</b></br>
1.กำหนด Return parameter ใน https://controls.paysolutions.asia/</br>
Post back URL : ให้ระบุค่า https://yoursite.com/?wc-api=WC_amdev_Paysolutions</br>
โดยเปลี่ยน yoursite เป็นเว็บไซต์ที่ใช้งาน</br>
Return URL : ระบุเว็บไซต์ที่งาน
<img src='https://www.thaiepay.com/images/woo39/woo39-1.png' > <br /><br />

2.ทำการติดตั้งปลั๊กอินและเปิดใช้งาน Payments ของ Woocommerce ดังรูป
<img src='https://www.thaiepay.com/images/woo39/woo39-2.png' > <br /><br />

3.ตั้งค่าปลั๊กอิน โดยระบุค่าดังนี้
<img src='https://www.thaiepay.com/images/woo39/woo39-3.png' > <br /><br />

1.Title : ข้อความที่แสดงชื่อการชำระเงินในหน้าชำระเงิน</br>
2.merchant Id : รหัสร้านค้าที่ได้จาก Paysolutions</br>
3.Merchant API Name และ Key: ใช้ค่าเดียวกันกับ merchant Id แล้วกดปุ่ม Save changes
