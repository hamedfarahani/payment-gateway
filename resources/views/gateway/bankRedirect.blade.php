<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <title>در حال انتقال به بانک</title>
</head>
<body>

<form action="{{route('web.bank.redirect',$paymentId)}}" method="get" id="redirectForm">

</form>

</body>
</html>

<script>
    window.onload = redirectToPerfectMoney();

    function redirectToPerfectMoney() {
        document.getElementById("redirectForm").submit();
    }
</script>