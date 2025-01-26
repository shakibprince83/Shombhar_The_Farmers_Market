document.addEventListener('DOMContentLoaded', () => {
  const userList = document.getElementById('userList');
  const messageList = document.getElementById('messageList');
  const messageText = document.getElementById('messageText');
  const sendMessageBtn = document.getElementById('sendMessage');

  let selectedUser = null;

  // Fetch list of messageable users
  async function fetchUserList() {
      try {
          const response = await fetch('get_users.php');
          const users = await response.json();
          
          userList.innerHTML = users.map(user => 
              `<div class="user-item" data-id="${user.id}" data-type="${user.type}">
                  ${user.name}
              </div>`
          ).join('');

          // Add click event to user items
          document.querySelectorAll('.user-item').forEach(item => {
              item.addEventListener('click', () => {
                  selectedUser = {
                      id: item.dataset.id,
                      type: item.dataset.type
                  };
                  loadMessages(selectedUser);
              });
          });
      } catch (error) {
          console.error('Error fetching users:', error);
      }
  }

  // Load messages with a specific user
  async function loadMessages(user) {
      try {
          const response = await fetch(`load_messages.php?user_id=${user.id}&user_type=${user.type}`);
          const messages = await response.json();

          messageList.innerHTML = messages.map(msg => 
              `<div class="message ${msg.sender_type === currentUserType ? 'sent-message' : 'received-message'}">
                  ${msg.message_text}
                  <small class="d-block text-muted">${new Date(msg.timestamp).toLocaleString()}</small>
              </div>`
          ).join('');

          // Scroll to bottom
          messageList.scrollTop = messageList.scrollHeight;
      } catch (error) {
          console.error('Error loading messages:', error);
      }
  }

  // Send a new message
  sendMessageBtn.addEventListener('click', async () => {
      if (!selectedUser || !messageText.value.trim()) return;

      try {
          const response = await fetch('send_message.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                  receiver_id: selectedUser.id,
                  receiver_type: selectedUser.type,
                  message_text: messageText.value
              })
          });

          const result = await response.json();
          if (result.success) {
              messageText.value = '';
              loadMessages(selectedUser);
          }
      } catch (error) {
          console.error('Error sending message:', error);
      }
  });

  // Fetch users on page load
  fetchUserList();

  // Periodically refresh messages
  setInterval(() => {
      if (selectedUser) {
          loadMessages(selectedUser);
      }
  }, 5000);
});