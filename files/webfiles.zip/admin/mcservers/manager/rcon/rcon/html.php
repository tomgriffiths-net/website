<!DOCTYPE HTML>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Minecraft RCON</title>
    <link rel="stylesheet" type="text/css" href="static/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="static/css/style.css">
    <script type="text/javascript" src="static/js/jquery-1.12.0.min.js"></script>
    <script type="text/javascript" src="static/js/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="static/js/jquery-ui-1.12.0.min.js"></script>
    <script type="text/javascript" src="static/js/bootstrap.min.js" ></script>
    <script type="text/javascript" src="static/js/script.js" ></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      body {
        margin: 0;
        font-family:DejaVu Sans Mono, monospace;
        font-weight: 600;
        color: white;
        text-align: left;
        background-color:#242324;
      }
    </style>
</head>
<body>
  <div style="position:absolute;left:2%;width:60%;height:80%">
    <div class="container-fluid" id="content">
      <div class="alert alert-info" id="alertMessage">
        Minecraft RCON
      </div>
      <div id="consoleRow">
        <div class="panel panel-default" id="consoleContent">
          <div class="panel-heading">
            <h3 class="panel-title pull-left"><span class="glyphicon glyphicon-console"></span> Console</h3>
          </div>
          <div class="panel-body">
            <ul class="list-group" id="groupConsole"></ul>
          </div>
        </div>
        <div class="input-group" id="consoleCommand">
          <span class="input-group-addon">
            <input id="chkAutoScroll" type="checkbox" checked="true" autocomplete="off" /><span class="glyphicon glyphicon-arrow-down"></span>
          </span>
          <div id="txtCommandResults"></div>
          <input type="text" class="form-control" id="txtCommand" />
          <div class="input-group-btn">
            <button type="button" class="btn btn-primary" id="btnSend"><span class="glyphicon glyphicon-send"></span><span class="hidden-xs"> Send</span></button>
            <button type="button" class="btn btn-warning" id="btnClearLog"><span class="glyphicon glyphicon-erase"></span><span class="hidden-xs"> Clear</span></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>