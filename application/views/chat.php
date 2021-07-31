<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <!-- vue js -->
    <script src="<?= base_url("assets/vue/qs.min.js") ?>" type="text/javascript"></script>
    <script src="<?= base_url("assets/vue/vue.min.js") ?>" type="text/javascript"></script>
    <script src="<?= base_url("assets/vue/axios.min.js") ?>" type="text/javascript"></script>
    <script src="<?= base_url("assets/vue/accounting.js") ?>" type="text/javascript"></script>
    <script src="<?= base_url("assets/vue/vue-numeric.min.js") ?>" type="text/javascript"></script>
    <script src="<?= base_url("assets/vue/lodash.min.js") ?>" type="text/javascript"></script>

    <!-- load jquery CDN -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

</head>
<style>
    body {
        padding-top: 10px;
    }

    table tr th {
        padding: 3px;
        border-collapse: separate;
    }
</style>

<body>
    <section class="chat">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12" id="form_">
                   
                    <div class="card">
                        <table class="tables" id="tes">
                        
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
<script>
    Vue.use(VueNumeric.default);
</script>
<script>
    var user = '<?=$user?>';
    var form_ = new Vue({
        el: '#form_',
        data: {
            detail: [],
            idUser: 2,
            interval: 0,
        },
        methods: {
            clearDetails: function() {
                this.detail = []
            },
            ready() {
                this.getData();
            },
            getData: function() {

                $.ajax({
                    url: '<?php echo base_url('api/getChatRoom') ?>',
                    type: 'POST',
                    data: {
                        idUser: this.idUser
                    },
                    cache: false,
                    dataType: "JSON",
                    success: function(response) {
                        showNotif(response); 
                    }
                });
            },
        },
        created() {
            this.interval = setInterval(() => this.getData(), 500);

        }
    })
    function showNotif(response) {
          $("").html('');
          $("#tes").html('');
          var html = '';
          for (rsp of response.values) {
            if(rsp.idFrom==user){
                html ='';
            }else{
                html +='<thead onClick="goToDetail(rsp.idRoom)" href="#"><tr ><th width="20%" rowspan="2"><img src="<?= base_url() ?>image/profil_user/default.png" alt="" width="50px"></th><th>'+ rsp.namaTujuan+'</th><th><h6>'+rsp.lastTimeTujuan+'</h6></th></tr><tr><th class="text-left" width="70%">'+rsp.lastMessageTujuan+'</p></th><th class="text-left"><span class="badge badge-danger">'+rsp.unReadMessageTujuan+'</span></th></tr></thead>';  
            }       
          }
          $("#tes").append(html);
        }

        function goToDetail(index){
          location.href= window.location +'/detail?index='+ index+'&id='+user;
            console.log(index);
        }

</script>

</html>