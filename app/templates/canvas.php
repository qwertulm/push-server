<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Desktops and Tablets</title>

  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

  <script type="text/javascript">
    $(document).ready(function () {
      initialize();
    });
    var previous_position = null;


    // works out the X, Y position of the click inside the canvas from the X, Y position on the page
    function getPosition(mouseEvent, sigCanvas) {
      var x, y;
      if (mouseEvent.pageX != undefined && mouseEvent.pageY != undefined) {
        x = mouseEvent.pageX;
        y = mouseEvent.pageY;
      } else {
        x = mouseEvent.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
        y = mouseEvent.clientY + document.body.scrollTop + document.documentElement.scrollTop;
      }

      return { X: x - sigCanvas.offsetLeft, Y: y - sigCanvas.offsetTop };
    }

    function initialize() {
      // get references to the canvas element as well as the 2D drawing context
      var sigCanvas = document.getElementById("canvasSignature");
      var context = sigCanvas.getContext("2d");
      context.strokeStyle = 'Black';

      // This will be defined on a TOUCH device such as iPad or Android, etc.
      var is_touch_device = 'ontouchstart' in document.documentElement;

      if (is_touch_device) {
        // create a drawer which tracks touch movements
        var drawer = {
          isDrawing: false,
          touchstart: function (coors) {
            context.beginPath();
            context.moveTo(coors.x, coors.y);
            this.isDrawing = true;
          },
          touchmove: function (coors) {
            if (this.isDrawing) {
              context.lineTo(coors.x, coors.y);
              context.stroke();
            }
          },
          touchend: function (coors) {
            if (this.isDrawing) {
              this.touchmove(coors);
              this.isDrawing = false;
            }
          }
        };

        // create a function to pass touch events and coordinates to drawer
        function draw(event) {
          console.log('+');

          // get the touch coordinates.  Using the first touch in case of multi-touch
          var coors = {
            x: event.targetTouches[0].pageX,
            y: event.targetTouches[0].pageY
          };

          // Now we need to get the offset of the canvas location
          var obj = sigCanvas;

          if (obj.offsetParent) {
            // Every time we find a new object, we add its offsetLeft and offsetTop to curleft and curtop.
            do {
              coors.x -= obj.offsetLeft;
              coors.y -= obj.offsetTop;
            }
              // The while loop can be "while (obj = obj.offsetParent)" only, which does return null
              // when null is passed back, but that creates a warning in some editors (i.e. VS2010).
            while ((obj = obj.offsetParent) != null);
          }

          // pass the coordinates to the appropriate handler
          drawer[event.type](coors);
        }


        // attach the touchstart, touchmove, touchend event listeners.
        sigCanvas.addEventListener('touchstart', draw, false);
        sigCanvas.addEventListener('touchmove', draw, false);
        sigCanvas.addEventListener('touchend', draw, false);

        // prevent elastic scrolling
        sigCanvas.addEventListener('touchmove', function (event) {
          event.preventDefault();
        }, false);
      }
      else {

        // start drawing when the mousedown event fires, and attach handlers to
        // draw a line to wherever the mouse moves to
        $("#canvasSignature").mousedown(function (mouseEvent) {
          var position = getPosition(mouseEvent, sigCanvas);

          context.moveTo(position.X, position.Y);
          context.beginPath();

          // attach event handlers
          $(this).mousemove(function (mouseEvent) {
            drawLine(mouseEvent, sigCanvas, context);
          }).mouseup(function (mouseEvent) {
            finishDrawing(mouseEvent, sigCanvas, context);
          }).mouseout(function (mouseEvent) {
            finishDrawing(mouseEvent, sigCanvas, context);
          });
        });

      }
    }

    // draws a line to the x and y coordinates of the mouse event inside
    // the specified element using the specified context
    function drawLine(mouseEvent, sigCanvas, context) {

      var position = getPosition(mouseEvent, sigCanvas);
//      console.log(context);
      if (previous_position) {
        sendMessage(form.getAttribute('action'), previous_position.X + ' ' + previous_position.Y + ' ' + position.X + ' ' + position.Y);
        previous_position = position;
      } else {
        previous_position = position;
      }
//      draw_pixel(position.X, position.Y);
//      context.lineTo(position.X, position.Y);
//      context.stroke();
    }

    function draw_pixel(a, b, x, y) {
      var sigCanvas = document.getElementById("canvasSignature");
      var context = sigCanvas.getContext("2d");
      context.beginPath();
      context.moveTo(a, b);
      context.lineTo(x, y);
      context.stroke();
    }

    // draws a line from the last coordiantes in the path to the finishing
    // coordinates and unbind any event handlers which need to be preceded
    // by the mouse down event
    function finishDrawing(mouseEvent, sigCanvas, context) {
      // draw the line to the finishing coordinates
      drawLine(mouseEvent, sigCanvas, context);
      previous_position = null;

      context.closePath();

      // unbind any events which could draw
      $(sigCanvas).unbind("mousemove")
        .unbind("mouseup")
        .unbind("mouseout");
    }
  </script>

</head>

<body style="margin: 0;">
<canvas id="canvasSignature" style="margin: 0;"></canvas>
<script>
  $('#canvasSignature').attr('height', $(window).height() -4 + 'px');
  $('#canvasSignature').attr('width', $(window).width() + 'px');
</script>
</body>
</html>































<form style="display: none;" action="http://localhost:9080/pub?id=default"
      subscribe_commet_url="http://localhost:9080/sub/default"
      subscribe_web_socket_url="ws://localhost:9080/ws/default">
  <select name="select">
    <option>select connection</option>
    <option>comet</option>
    <option>webSocket</option>
  </select>
  <input type="text" name="message" autocomplete="off">
  <input type="submit" value="send">
</form>
<script type="text/javascript">
  form = document.getElementsByTagName('form')[0];
  var connections = {};
//  form.select.onchange = function () {
//    closeConnections();
//    if (form.select.value == 'comet') {
//      SubscribeCommet(document.getElementsByTagName('body')[0], form.getAttribute('subscribe_commet_url'));
//    }
//
//    if (form.select.value == 'webSocket') {
      SubscribeWebSocket(document.getElementsByTagName('body')[0], form.getAttribute('subscribe_web_socket_url'));
//    }
//  };
  PublishForm(form, form.getAttribute('action'));

  function sendMessage(url, message) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    // просто отсылаю сообщение "как есть" без кодировки
    // если бы было много данных, то нужно было бы отослать JSON из объекта с ними
    // или закодировать их как-то иначе
    xhr.send(message);
  }

  // Посылка запросов -- обычными XHR POST
  function PublishForm(form, url) {


    form.onsubmit = function () {
      var message = form.message.value;
      if (message) {
        form.message.value = '';
        sendMessage(url, message);
      }
      return false;
    };
  }

  function showMessage(elem, message) {
    var array = message.toString().split(' ');
    draw_pixel(array[0], array[1], array[2], array[3]);
  }

  // Получение сообщений, COMET
  function SubscribeCommet(elem, url) {
    loadedLen = 0;
    connections.commet = connections.commet || new XMLHttpRequest();

    function subscribe() {
      connections.commet.onreadystatechange = function () {
        if (this.readyState != 3) return;

        if (this.status == 200) {
          if (this.responseText) {
            // сервер может закрыть соединение без ответа при перезагрузке
            showMessage(elem, JSON.parse(this.responseText.substr(loadedLen)).text);
            loadedLen = this.responseText.length;
          }
          subscribe();
        }
      };
    }

    subscribe();
    connections.commet.open("GET", url, true);
    connections.commet.send();
  }

  // Получение сообщений, webSocket
  function SubscribeWebSocket(elem, url) {
    connections.socket = connections.socket || new WebSocket(url);

    connections.socket.onopen = function () {
      console.log("Соединение установлено.");
    };

    connections.socket.onclose = function (event) {
      if (event.wasClean) {
        console.log('Соединение закрыто чисто');
      } else {
        console.log('Обрыв соединения'); // например, "убит" процесс сервера
      }
      console.log('Код: ' + event.code + ' причина: ' + event.reason);
    };

    connections.socket.onmessage = function (event) {
      showMessage(elem, JSON.parse(event.data).text);
    };

    connections.socket.onerror = function (error) {
      console.log("Ошибка " + error.message);
    };
  }

  function closeConnections() {
    if (connections.commet) {
      connections.commet.abort();
      connections.commet = null;
    }

    if (connections.socket) {
      connections.socket.close();
      connections.socket = null;
    }
  }







  function explode( delimiter, string ) {	// Split a string by string
                                           //
                                           // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
                                           // +   improved by: kenneth
                                           // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)

    var emptyArray = { 0: '' };

    if ( arguments.length != 2
      || typeof arguments[0] == 'undefined'
      || typeof arguments[1] == 'undefined' )
    {
      return null;
    }

    if ( delimiter === ''
      || delimiter === false
      || delimiter === null )
    {
      return false;
    }

    if ( typeof delimiter == 'function'
      || typeof delimiter == 'object'
      || typeof string == 'function'
      || typeof string == 'object' )
    {
      return emptyArray;
    }

    if ( delimiter === true ) {
      delimiter = '1';
    }

    return string.toString().split ( delimiter.toString() );
  }

</script>
