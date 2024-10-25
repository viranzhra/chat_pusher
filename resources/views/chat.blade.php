@extends('layouts.app')

@section('content')
    <style>
        /* Style scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #eee;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .main {
            background-color: #eee;
            width: 100%;
            max-width: 500px;
            /* Batasi lebar maksimum */
            position: relative;
            border-radius: 8px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            padding: 6px 0px;
            /* Atur padding */
            margin: 0 10px;
            /* Memberikan margin agar tidak menyentuh tepi */
        }

        .header {
            background-color: #f8f9fa; /* Light gray background */
            border-bottom: 1px solid #dee2e6; /* Bottom border for separation */
        }

        .header h5 {
            margin: 0; /* Remove default margin */
            font-weight: 600; /* Bold text */
        }

        .delete-chat {
            display: flex;
            align-items: center; /* Center icon and text vertically */
            transition: background-color 0.3s, color 0.3s; /* Smooth transition for hover */
            cursor: pointer;
            color: red;
            font-size: 15px;
        }

        .delete-chat:hover {
            background-color: #dc3545; /* Darker red on hover */
            color: #fff; /* White text on hover */
            border-radius: 5px; /* Rounded corners */
            padding: 5px 10px; /* Padding for button effect */
        }

        .btn-danger {
            background-color: #dc3545; /* Bootstrap's danger color */
            border-color: #dc3545; /* Border color matching background */
        }

        .btn-danger:hover {
            background-color: #c82333; /* Darker red on hover */
            border-color: #bd2130; /* Darker border on hover */
        }

        .scroll {
            overflow-y: auto;
            scroll-behavior: smooth;
            height: 325px;
            margin-bottom: 10px;
            /* Menambah ruang antara scroll dan input */
        }

        .name {
            font-size: 8px;
        }

        .msg {
            font-size: 11px;
            padding: 8px;
            border-radius: 5px;
            font-weight: 500;
            position: relative;
            margin-bottom: 5px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .user-msg {
            background-color: #007bff;
            /* Blue for the logged-in user */
            color: white;
            margin-left: auto;
        }

        .other-msg {
            background-color: #fff;
            color: #3e3c3c;
            margin-right: auto;
        }

        .chat-message {
            display: flex;
            justify-content: flex-start;
            /* Default alignment for other users */
        }

        .chat-message.user {
            justify-content: flex-end;
            /* Align messages from the user to the right */
        }

        .timestamp {
            font-size: 10px;
            color: rgba(33, 31, 31, 0.67);
            text-align: right;
            margin-top: 4px;
            margin-left: auto;
        }

        #form {
            transition: box-shadow 0.3s ease;
        }

        #form:hover {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

    </style>

    <div class="d-flex justify-content-center container mt-5">
        <div class="wrapper">
            <div class="main" style="padding-top: 0px; padding-bottom: 0px;">
                <div class="header d-flex justify-content-between align-items-center p-2 shadow-sm" style="background-color: #f8fafc; border-bottom: 1px solid #dee2e6; border-radius-top: 10px;">
                    <h5 class="m-0">Chat Room</h5> <!-- Optional chat room title -->
                    <form id="deleteChatForm" class="m-0">
                        <button type="button" id="deleteChat" class="btn btn-danger btn-sm" title="Hapus Semua Chat">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>                                
                <div class="px-2 scroll" id="message"></div>
                <form id="form" class="navbar bg-white navbar-expand-sm d-flex align-items-center p-2 shadow-sm" style="border-radius: 8px;">
                    <input type="text" name="text" class="form-control me-2" placeholder="Ketik pesan..." required style="border-radius: 50px;">
                    <button style="border-radius: 50%; padding: 5px; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;" type="submit" aria-label="Kirim Pesan">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#14276f" viewBox="0 0 256 256">
                            <path d="M240,127.89a16,16,0,0,1-8.18,14L63.9,237.9A16.15,16.15,0,0,1,56,240a16,16,0,0,1-15-21.33l27-79.95A4,4,0,0,1,71.72,136H144a8,8,0,0,0,8-8.53,8.19,8.19,0,0,0-8.26-7.47h-72a4,4,0,0,1-3.79-2.72l-27-79.94A16,16,0,0,1,63.84,18.07l168,95.89A16,16,0,0,1,240,127.89Z"></path>
                        </svg>
                    </button>
                </form>                                               
            </div>
        </div>
    </div>

    {{-- Load Pusher library --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script>
        // Function to append new chat messages
        const appendChat = (user_name, message, isUser) => {
            const messageContainer = document.getElementById('message');
            const timestamp = new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
            const chatHTML = `
            <div class="chat-message ${isUser ? 'user' : ''}">
                <div class="${isUser ? 'text-right' : ''} pr-2 ${isUser ? '' : 'pl-1'}"> 
                    <span class="name">${user_name}</span>
                    <p class="msg ${isUser ? 'user-msg' : 'other-msg'}">
                        ${message}
                        <span class="timestamp">${timestamp}</span>
                    </p>
                </div>
            </div>`;
            messageContainer.insertAdjacentHTML('beforeend', chatHTML);
            // Scroll to the bottom after appending a new message
            messageContainer.scrollTop = messageContainer.scrollHeight;
        };

        // Get chat from API
        const getChat = async () => {
            const response = await fetch('/chat/get/{{ $room->id }}');
            const data = await response.json();
            document.getElementById('message').innerHTML = ''; // Clear the previous chat

            // Render all chat messages
            data.forEach(r => {
                appendChat(r.user_name, r.message, r.user_id == "{{ Auth::user()->id }}");
            });
        };

        // Hapus semua chat
        document.getElementById('deleteChat').addEventListener('click', async (event) => {
            event.preventDefault(); // Prevent the default form submission

            if (confirm("Apakah Anda yakin ingin menghapus semua chat?")) {
                const roomId = '{{ $room->id }}'; // Ensure this is the correct room ID
                const response = await fetch(`/chat/delete-all/${roomId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}', // Ensure CSRF token is included
                        'Content-Type': 'application/json'
                    }
                });

                const result = await response.json(); // Wait for JSON response

                if (response.ok) {
                    document.getElementById('message').innerHTML = ''; // Clear the message container
                    alert(result.message); // Show success message
                } else {
                    alert(result.message ||
                    'Gagal menghapus chat.'); // Show specific error message if available
                }
            }
        });


        window.addEventListener('load', async (ev) => {
            await getChat();

            // Koneksi ke Pusher
            const pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
                cluster: "{{ env('PUSHER_APP_CLUSTER') }}"
            });

            // Berlangganan ke channel chat
            const channel = pusher.subscribe('chat-channel');

            // Mendengarkan event chat-send
            channel.bind('chat-send', async (data) => {
                appendChat(data.user_name, data.message, data.user_id == "{{ Auth::user()->id }}");
            });

            // Mengirim pesan
            document.getElementById('form').addEventListener('submit', async (ev) => {
                ev.preventDefault();

                const messageInput = document.querySelector('input[name="text"]');
                const message = messageInput.value;

                const response = await fetch('/chat/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        message: message,
                        room: '{{ $room->id }}'
                    })
                });

                const data = await response.json();

                if (data) {
                    appendChat("{{ Auth::user()->name }}", message, true);
                    messageInput.value = ''; // Clear the input field
                }
            });

            // Update timestamps every minute
            setInterval(() => {
                const messages = document.querySelectorAll('.msg');
                messages.forEach(msg => {
                    const timestamp = new Date().toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    const timestampElement = msg.querySelector('.timestamp');
                    if (timestampElement) {
                        timestampElement.textContent = timestamp;
                    }
                });
            }, 60000);
        });
    </script>
@endsection
