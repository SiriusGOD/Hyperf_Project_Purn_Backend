

function confirm_user(url , msg){
  if (confirm(msg)) {
    // 使用者按下確認按鈕
    // 執行相應的程式碼，例如導向到指定的URL
    window.location.href = url;
  }

}
