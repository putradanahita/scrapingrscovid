<?php

//load modul simple html dom
require_once 'assets/simple_html_dom.php';
    
    //web yang akan di scraping
    $url = 'https://yankes.kemkes.go.id/app/siranap/rumah_sakit?jenis=1&propinsi=32prop&kabkota=';
    // harus menggunakan curl agar bisa berjalan di Hosting.
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $curl_scraped_page = curl_exec($ch);
    $html = new simple_html_dom();
    $html->load($curl_scraped_page);

//proses scraping data
$article = array();
if(!empty($html)) {
$div_class = $title = "";
$i = 0;

  //Mencari nama rumah sakit dari class cardRS
foreach($html->find(".cardRS") as $div_class) {
  foreach($div_class->find(".col-md-7 h5") as $title ) {
    $article[$i]['title'] = $title->plaintext;
  }
  // Mencari alamat rumah sakit dari class col-md-7 tag p
  foreach($div_class->find(".col-md-7 p") as $alamat ) {
    $article[$i]['alamat'] = $alamat->plaintext;
  }
  // mencari ketersediaan kamar dari class col-md-5 tag p
  foreach($div_class->find(".col-md-5 p") as $detail ) {
    $article[$i]['detail'] = $detail->plaintext;
  }
   //jumlah kamar tersedia diambil dari class col-md-5 dan kebetulan mempunyai tag bold / b
   foreach ($div_class->find('.col-md-5') as $kamar) {
    if(isset($kamar)){
    //saya filter lagi jika ada kata "lain" maka artinya kamar kosong. Diambil dari kata lain waktu.
    if (strpos($kamar, 'lain') == true)
    {
        $article[$i]['kamar'] = "0";
      }
      else{
        // jika tidak ada kata "lain" maka find tag b yang menunjukan ketersediaan kamar. 
        foreach($kamar->find('b') as $tag)
        {
           $article[$i]['kamar'] = $tag->plaintext;
        }
      }
    
    }
  }
  //mencari nomor telpon rumah sakit dari tag span
  foreach($div_class->find("<span>") as $call ) {
    $article[$i]['call'] = trim($call->plaintext);
  }
  $i++;
}
}
//proses array
//echo '<pre>';
//print_r($article); 
$info = $article;
//hanya validasi json
//echo json_encode($info);
?>

<!-- data ditampilkan menggunakan datatables -->
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AdminLTE 3 | DataTables</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini">

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-12">
          <div class="card">
          <div class="card-header">
              <h3 class="card-title"><strong>Seluruh RS Provinsi Jawa Barat</strong></h3>
            </div>
            <div class="card-body">
              <table id="my-table" class="table table-bordered table-hover">
                <thead>
            <tr>
                <th>Rumah Sakit</th>
                <th>Phone</th>
                <th>Sisa Kamar</th>
                <th>Update Terakhir</th>
                <th>Alamat</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
             <th>Nama Rumah Sakit</th>
                <th>Phone</th>
                <th>Sisa Kamar</th>
                <th>Update Terakhir</th>
                <th>Alamat</th>
                
            </tr>
        </tfoot>
    </table>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->

          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
<!-- jQuery -->
<script src="assets/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="assets/dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="assets/dist/js/demo.js"></script>
<!-- page script -->


<script type="text/javascript">
//ambil data json dari data array yang telah di buat di atas. 
var information = <?php echo json_encode($info) ?>;
$(document).ready(function () {
    $('#my-table').dataTable({
    //properties datatable responsive, order by kolom no 2 (ketersdiaan kamar)   
    "responsive": true,
      "autoWidth": false,
      order: [[2, 'desc']],
              "columnDefs": [ {
        "targets": 2,
        "className": "text-center",
         } ],
        data: information,
        columns: [
            { data: 'title', title: 'Rumah Sakit' },
            { data: 'call',"render": function(data, type, row, meta) 
            {
            if(type === 'display')
            {
                data = '<a href="tel:' + data + '"><button class="btn btn-block btn-success btn-sm"><i class="fa fa-phone"></i> Call</button></a>';
            }

            return data;
         } 
      },
            
            { data: 'kamar', title: 'Sisa Kamar' },
            { data: 'detail', title: 'Update Terakhir' },
            { data: 'title',"render": function(data, type, row, meta) 
            {
            if(type === 'display')
            {
             data = '<a class="urlbiasa" target="_blank" href="https://maps.google.com/?q=' + data + '"><button class="btn btn-block btn-success btn-sm"><i class="fa fa-map-marker" aria-hidden="true"></i> Maps</button></a>';
            }

            return data;
         } 
      }
            ]

    });
});

    </script>
</body>
</html>
