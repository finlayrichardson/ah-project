var server = new WebSocket("ws://localhost:8001");

$(document).ready(function () {
  ctrlDown = false;
  $("input")
    .on("keydown", function (e) {
      if (e.keyCode == 13) {
        server.send($(this).val());
      }
      if (e.keyCode == 17 || e.keyCode == 91) ctrlDown = true;})
      .on("keyup", function (e) {
        if (e.keyCode == 17 || e.keyCode == 91) ctrlDown = false;
      });
  
  if (ctrlDown && (e.keyCode == 67)) console.log("Document catch Ctrl+C");
    

  server.onmessage = function (event) {
    // var reader = new FileReader();
    // reader.onload = function () {
    //   $("#result").append(reader.result.replaceAll("\n", "<br>") + "<br>");
    // };
    // reader.readAsText(event.data);

    $("#result").append(event.data.replaceAll("\n", "<br>") + "<br>");
  };
});
