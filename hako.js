//======================================================================
// トップページ用
// 次のターンまでの時間
function remainTime(nextTime) {
 var now = new Date();
 var remain = nextTime - Math.floor(now / 1000);
 if(remain < 0) {
   var hour = "00";
   var min  = "00";
   var sec  = "00";
 } else {
   var hour = Math.floor(remain / 3600);
   var min  = Math.floor(remain % 3600 / 60);
   var sec  = Math.floor(remain % 3600 % 60);
   if(min < 10) { min = "0" + min; }
   if(sec < 10) { sec = "0" + sec; }
 }
 document.write(hour + ' 時間 ' + min + ' 分 ' + sec + ' 秒)');
}

//======================================================================
// 開発画面用
//======================================================================
// 開発、観光画面
function Navi(position, img, title, pos, text, exp) {
  StyElm = document.getElementById("NaviView");
  StyElm.style.visibility = "visible";
  if(position == 1) {
    StyElm.style.marginLeft = -20;
  } else {
    StyElm.style.marginLeft = 240;
  }
  StyElm.innerHTML = "<div class='NaviTitle'>" + title + " " + pos + "<\/div><img class='NaviImg' src=" + img + "><div class='NaviText'>" + text.replace("\n", "<br>") + "<\/div>";
  if(exp) {
    StyElm.innerHTML += "<div class='NaviText'>" + eval(exp) + "<\/div>";
  }
}
function NaviClose() {
  StyElm = document.getElementById("NaviView");
  StyElm.style.visibility = "hidden";
}
