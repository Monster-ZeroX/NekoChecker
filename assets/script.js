function Stop() {
  location.reload();
}

function Start() {
  var cvv = 0;
  var ccn = 0;
  var dead = 0;
  var sk = $("#sk").val();
  if (sk == "") {
    alert("Please enter a SK key.");
    return;
  }
  var sktype = $("#sktype").val();
  if (sktype == "select") {
    alert("Please select SK type.");
    return;
  }
  var timeout = $("#timeout").val();
  var amount = $("#amount").val().split("_");
  var currency = amount[1];
  var amount = amount[0];
  var cards = $("#cards");
  if (cards.val() == null || cards.val() == "") {
    alert("Please Put Cards in Text Box");
    return;
  }
  var cards = cards.val().split("\n");
  $("#start_btn").attr("disabled", "disabled");
  cards.forEach(function(value, index) {
    setTimeout(function() {
      $.ajax({
        url: "check.php?sk=" + sk + "&amount=" + amount + "&currency=" + currency + "&card=" + value,
        type: "GET",
        success: function(responce) {
          if (responce.includes("CCN")) {
            ccn++;
            AppendResult(responce, "ccn", ccn);
          } else if (responce.includes("CVV")) {
            cvv++;
            AppendResult(responce, "cvv", cvv);
          } else {
            dead++;
            AppendResult(responce, "dead", dead);
          }
          RemoveLine();
        }
      });
    }, timeout * index);
  })
}

function AppendResult(result, which, count) {
  var cvv = $("#cvv_text");
  var ccn = $("#ccn_text");
  var dead = $("#dead_text");
  if (which == "cvv") {
    cvv.append(`${result}<br>`);
    $("#cvv_btn").text(`CVV: ${count}`);
  } else if (which == "ccn") {
    ccn.append(`${result}<br>`);
    $("#ccn_btn").text(`CCN: ${count}`);
  } else {
    dead.append(`${result}<br>`);
    $("#dead_btn").text(`DEAD: ${count}`);
  }
}

function ShowHide(button) {
  $("#cvv_btn").attr("class", "button");
  $("#ccn_btn").attr("class", "button");
  $("#dead_btn").attr("class", "button");
  $("#cvv_text").css("display", "none");
  $("#ccn_text").css("display", "none");
  $("#dead_text").css("display", "none");
  var btn = $("#" + button);
  if (button == "cvv_btn") {
    btn.addClass("is-success is-selected");
    $("#cvv_text").css("display", "block");
  } else if (button == "ccn_btn") {
    btn.addClass("is-info is-selected");
    $("#ccn_text").css("display", "block");
  } else {
    btn.addClass("is-danger is-selected");
    $("#dead_text").css("display", "block");
  }
}

function RemoveLine() {
  var cards = $("#cards").val().split("\n");
  cards.splice(0, 1);
  $("#cards").val(cards.join("\n"));
}