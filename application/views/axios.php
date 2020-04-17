<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>PinoyWAP</title>
  <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" crossorigin="anonymous">
  <script src="<?php echo BASE_URL; ?>assets/js/vue.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand fixed-top navbar-default">
  <!-- Brand/logo -->
  <a class="navbar-brand" href="#">PinoyWAP</a>

  <!-- Links -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item pr-4 d-none d-sm-block">
      <a class="nav-link font-weight-bold" href="#">Ron</i></a>
    </li>
    <li class="nav-item pr-4">
      <a class="nav-link" href="#"><i class="fas fa-envelope fa-lg"></i></a>
    </li>
    <li class="nav-item pr-4">
      <a class="nav-link" href="#"><i class="fas fa-bell fa-lg"></i></a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#"><i class="fas fa-user-friends fa-lg"></i></a>
    </li>
  </ul>
</nav>
<div class="container" id="root">
  <div class="row">
    <div class="col-md-3 mb-3 d-none d-sm-block">
      <ul class="list-group list-group-flush">
        <li class="list-group-item">Cras justo odio</li>
        <li class="list-group-item">Dapibus ac facilisis in</li>
        <li class="list-group-item">Morbi leo risus</li>
        <li class="list-group-item">Porta ac consectetur ac</li>
        <li class="list-group-item">Vestibulum at eros</li>
      </ul>
    </div>
    <div class="col-md-6 mb-3">
      <div class="card">
        <div class="card-header">
          What's on your mind?
        </div>
        <div class="card-body">
          <form>
            <div class="form-group">
              <textarea class="form-control" id="status" rows="3" v-model="message"></textarea>
            </div>
            <button type="submit" class="btn btn-my-primary float-right">Share</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3 d-none d-sm-block">
      <div class="card">
        <div class="card-body">{{ message }}</div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-3 mb-3 d-none d-sm-block">
      <ul class="list-group list-group-flush">
        <li class="list-group-item">Cras justo odio</li>
        <li class="list-group-item">Dapibus ac facilisis in</li>
        <li class="list-group-item">Morbi leo risus</li>
        <li class="list-group-item">Porta ac consectetur ac</li>
        <li class="list-group-item">Vestibulum at eros</li>
      </ul>
    </div>
    <div class="col-md-6 mb-3">
      <div class="card">
        <div class="card-body">Basic card</div>
      </div>
    </div>
    <div class="col-md-3 mb-3 d-none d-sm-block">
      <div class="card">
        <div class="card-body">Basic card</div>
      </div>
    </div>
  </div>
</div>
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
<script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/bootstrap.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/app.js"></script> 
</body>
</html>