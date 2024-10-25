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
            position: relative;
            border-radius: 8px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            padding: 6px 0px;
            margin: 0 10px;
        }

        .header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .header h5 {
            margin: 0;
            font-weight: 600;
        }

        .delete-chat {
            display: flex;
            align-items: center;
            transition: background-color 0.3s, color 0.3s;
        }

        .delete-chat:hover {
            background-color: #dc3545;
            color: #fff;
            border-radius: 5px;
            padding: 5px 10px;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .scroll {
            overflow-y: auto;
            scroll-behavior: smooth;
            height: 325px;
            margin-bottom: 10px;
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
        }

        .chat-message.user {
            justify-content: flex-end;
        }

        .timestamp {
            font-size: 10px;
            color: rgba(33, 31, 31, 0.67);
            text-align: right;
            margin-top: 4px;
            margin-left: auto;
        }

        .delete-chat {
            cursor: pointer;
            color: red;
            font-size: 15px;
        }
    </style>

    <div class="d-flex justify-content-center container mt-5">
        <div class="wrapper">
            <div class="main" style="padding: 5px;">
                <div class="header d-flex justify-content-between align-items-center p-2">
                    <h5 class="m-0">Chat Room</h5>
                    <form id="deleteChatForm" class="m-0">
                        <button type="button" id="deleteChat" class="btn btn-danger btn-sm" title="Hapus Semua Chat">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                <div class="px-2 scroll" id="message"></div>
                <form id="form" class="navbar bg-white navbar-expand-sm d-flex justify-content-between" style="padding: 10px;">
                    <input type="text" name="text" class="form-control" placeholder="Type a message..." required>
                    <button class="btn btn-success" style="border-radius: 60%;" type="submit" aria-label="Kirim Pesan">
                        <img src="{{ asset('images/paper-plane-top.png') }}" alt="Kirim" style="width: 17px; height: 17px; vertical-align: middle; font-size: 10px;">
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal hapus isi chat --}}
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus semua chat?
                </div>
                <div class="modal-footer">
                    {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button> --}}
                    <button type="button" id="confirmDeleteChat" class="btn btn-danger">Hapus</button>
                </div>
            </div>
        </div>
    </div>

        

    {{-- Toast Notification --}}
    <div aria-live="polite" aria-atomic="true" style="position: relative; z-index: 1051;">
        <div class="toast" id="successToast" style="position: absolute; top: 20px; right: 20px;" data-delay="3000">
            <div class="toast-header">
                <strong class="mr-auto">Notifikasi</strong>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body">
                <span id="toastMessage">Pesan berhasil dikirim!</span>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    {{-- Load Pusher library --}}
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script>
        // Function to append new chat messages
        const appendChat = (user_name, message, isUser) => {
            const messageContainer = document.getElementById('message');
            const timestamp = new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
            const chatHTML = 
            <div class="chat-message ${isUser ? 'user' : ''}">
                <div class="${isUser ? 'text-right' : ''} pr-2 ${isUser ? '' : 'pl-1'}"> 
                    <span class="name">${user_name}</span>
                    <p class="msg ${isUser ? 'user-msg' : 'other-msg'}">
                        ${message}
                        <span class="timestamp">${timestamp}</span>
                    </p>
                </div>
            </div>;
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

        $(document).ready(function () {
            // Show confirmation modal for deleting chat
            $('#deleteChat').on('click', function () {
                $('#confirmDeleteModal').modal('show'); // Show the modal
            });

            // Handle the 'Batal' button click
            $('.close[data-dismiss="modal"]').on('click', function () {
                $('#confirmDeleteModal').modal('hide'); // Hide the modal programmatically
            });

            // Hapus semua chat
            $('#confirmDeleteChat').on('click', async function () {
                const roomId = '{{ $room->id }}'; // Ensure this is the correct room ID
                const response = await fetch(/chat/delete-all/${roomId}, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });

                const result = await response.json(); // Wait for JSON response

                $('#confirmDeleteModal').modal('hide'); // Hide the modal

                if (response.ok) {
                    document.getElementById('message').innerHTML = ''; // Clear the chat messages
                    showToast("Semua pesan telah dihapus!"); // Show success toast
                } else {
                    showToast("Gagal menghapus pesan!"); // Show error toast
                }
            });
        });

        // Function to show toast notifications
        const showToast = (message) => {
            document.getElementById('toastMessage').innerText = message; // Set the message
            $('#successToast').toast('show'); // Show the toast
        };

        // Initial fetch of chat messages
        getChat();

        // Handle message sending
        document.getElementById('form').addEventListener('submit', async (e) => {
            e.preventDefault(); // Prevent default form submission

            const messageInput = e.target.text.value; // Get the message input
            if (messageInput.trim() === "") return; // Prevent sending empty messages

            const response = await fetch(/chat/send/${'{{ $room->id }}'}, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: messageInput })
            });

            if (response.ok) {
                appendChat("You", messageInput, true); // Append the user's message
                e.target.reset(); // Clear the input
                showToast("Pesan berhasil dikirim!"); // Show success toast
            } else {
                showToast("Gagal mengirim pesan!"); // Show error toast
            }
        });

        // Setup Pusher to listen for new messages
        const pusher = new Pusher('YOUR_PUSHER_APP_KEY', {
            cluster: 'YOUR_PUSHER_APP_CLUSTER',
            encrypted: true
        });

        const channel = pusher.subscribe('chat.' + '{{ $room->id }}'); // Subscribe to the room's channel
        channel.bind('new-message', (data) => {
            appendChat(data.user_name, data.message, false); // Append new message
        });
    </script>
@endsection