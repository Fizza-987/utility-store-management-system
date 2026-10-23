function toggleChat() {
  const box = document.getElementById('chatBox');
  const replyBox = document.getElementById('chatReply');
  
  if (box.style.display === 'block') {
    box.style.display = 'none';
    replyBox.style.display = 'none'; // reset answer when closed
  } else {
    box.style.display = 'block';
  }
}

function showAnswer(faqId) {
  const replyBox = document.getElementById('chatReply');
  replyBox.style.display = 'block';

  if (faqId === 1) {
    replyBox.innerHTML = "💡 <b>Stock Add karne ke liye:</b><br>'Inventory' page par jayein, product select karke 'Stock IN' select karein aur quantity daal kar Save kar dein.";
  } else if (faqId === 2) {
    replyBox.innerHTML = "📊 <b>Reports dekhne ke liye:</b><br>Sidebar me 'Reports' option par click karein, wahan saare stock transactions ki detail mil jayegi.";
  } else if (faqId === 3) {
    replyBox.innerHTML = "⚠️ <b>Low Stock Warning:</b><br>Jab kisi product ka stock 5 se kam hoga toh system usko 'Low Stock' me count karega.";
  }
}