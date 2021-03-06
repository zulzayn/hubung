///////////////////////////////////////////////////////////////////////
//for footer
if (document.querySelector('#footer-bar')) {
    // if (window.location.href.indexOf("home") > -1) 
    // {
    //     $('#notification-footer').removeClass('color-highlight');
    //     $('#home-footer').addClass('active-nav');
    //     $('#chat-footer').removeClass('active-nav');
    //     $('#meet-footer').removeClass('active-nav');
    //     $('#stream-footer').removeClass('active-nav');
    //     $('#file-footer').removeClass('active-nav');
    //     $('#setting-footer').removeClass('active-nav');
    // }
    if (window.location.href.indexOf("chat") > -1) 
    {
        $('#notification-footer').removeClass('color-highlight');
        $('#home-footer').removeClass('active-nav');
        $('#chat-footer').addClass('active-nav');
        $('#meet-footer').removeClass('active-nav');
        $('#stream-footer').removeClass('active-nav');
        $('#file-footer').removeClass('active-nav');
        $('#setting-footer').removeClass('active-nav');
    }
    else if(window.location.href.indexOf("meet") > -1)
    {
        $('#notification-footer').removeClass('color-highlight');
        $('#home-footer').removeClass('active-nav');
        $('#chat-footer').removeClass('active-nav');
        $('#meet-footer').addClass('active-nav');
        $('#stream-footer').removeClass('active-nav');
        $('#file-footer').removeClass('active-nav');
        $('#setting-footer').removeClass('active-nav');
    }
    else if(window.location.href.indexOf("stream") > -1)
    {
        $('#notification-footer').removeClass('color-highlight');
        $('#home-footer').removeClass('active-nav');
        $('#chat-footer').removeClass('active-nav');
        $('#meet-footer').removeClass('active-nav');
        $('#stream-footer').addClass('active-nav');
        $('#file-footer').removeClass('active-nav');
        $('#setting-footer').removeClass('active-nav');
    }
    else if(window.location.href.indexOf("file") > -1)
    {
        $('#notification-footer').removeClass('color-highlight');
        $('#home-footer').removeClass('active-nav');
        $('#chat-footer').removeClass('active-nav');
        $('#meet-footer').removeClass('active-nav');
        $('#stream-footer').removeClass('active-nav');
        $('#file-footer').addClass('active-nav');
        $('#setting-footer').removeClass('active-nav');
    }
    else if(window.location.href.indexOf("setting") > -1)
    {
        $('#notification-footer').removeClass('color-highlight');
        $('#home-footer').removeClass('active-nav');
        $('#chat-footer').removeClass('active-nav');
        $('#meet-footer').removeClass('active-nav');
        $('#stream-footer').removeClass('active-nav');
        $('#file-footer').removeClass('active-nav');
        $('#setting-footer').addClass('active-nav');
    }
    // else if(window.location.href.indexOf("notification") > -1)
    // {
    //     $('#notification-footer').addClass('color-highlight');
    //     $('#home-footer').removeClass('active-nav');
    //     $('#chat-footer').removeClass('active-nav');
    //     $('#meet-footer').removeClass('active-nav');
    //     $('#stream-footer').removeClass('active-nav');
    //     $('#file-footer').removeClass('active-nav');
    //     $('#setting-footer').removeClass('active-nav');
    // }
}
///////////////////////////////////////////////////////////////////////

//  setTimeout(function() {

    ///////////////////////////////////////////////////////////////////////
    // for append csrf-token
    if (document.querySelector('.csrf-token')) {
        ///////////////////////////////////////////////////////////////////////
        // fetch csrf token and append back to selected element
        fetch('/fetch/csrf').then(function(response) {
            return response.json();
        }).then(function(data) {

            var results = data

            document.querySelector('meta[name="csrf-token"]').setAttribute("content", results);
            $('.csrftoken').val(results);

        }).catch(function(err) {
            console.log('Error CSRF: ' + err);
        });
        ///////////////////////////////////////////////////////////////////////
    }
    ///////////////////////////////////////////////////////////////////////  

    ///////////////////////////////////////////////////////////////////////
    // for checking user auth
    if (document.querySelector('.check-auth')) {
        
        // var socket = io("http://localhost:3000/");
        // var socket = io("https://socket.zulzayn.com/");

        // fetch auth status and if no auth kick user to login
        fetch('/fetch/checkAuth').then(function(response) { 
            return response.json();
        }).then(function(data) {
            var results = data

            if(results.status === 'success')
            {
                document.querySelector('meta[name="id_user"]').setAttribute("content", results.user_id);

                if(window.location.href.indexOf("splashscreen") > -1){
                    swup.loadPage({
                        url: 'chat',
                        method: 'GET',
                        customTransition: '' 
                    });
                }
                else if(window.location.href.indexOf("meetroom") > -1)
                {
                    // console.log('Authenticated');
                    var url = new URL(window.location.href);
                    var roomId = url.searchParams.get("roomId");
                    if(roomId){
                        // window.location.href = 'meet?roomId='+roomId+'';
                        swup.loadPage({
                            url: 'meet?roomId='+roomId+'',
                            method: 'GET',
                            customTransition: '' 
                        });
                    }
                }
            }
            else
            {
                if(window.location.href.indexOf("meetroom") > -1)
                {
                    // console.log('Unauthenticated');
                }
                else
                {
                    swup.loadPage({
                        url: 'login?prevUrl='+window.location.pathname+'',
                        method: 'GET',
                        customTransition: '' 
                    });
                }
            }

            return results;
        }).then(function(results) {

            if(results.status === 'success')
            {
                console.log('masuk' , $('meta[name="id_user"]').attr('content'));
                //Save user online to socket 
                socket.emit('userOnline',{userId: $('meta[name="id_user"]').attr('content')});
            }
            else
            {

            }

        }).catch(function(err) {
            console.log('Error Check Auth: ' + err);
        });
    }
    ///////////////////////////////////////////////////////////////////////   

    ///////////////////////////////////////////////////////////////////////
    //for connect user button
    $('#logoutBtn').on('click' , function(event){

        if (navigator.onLine) {

            $('#logoutBtn').addClass('off-btn').trigger('classChange');

            fetch("/logout", {
                method: 'post',
                credentials: "same-origin",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            })
            .then(function(response){
                return response.json();
            }).then(function(resultsJSON){

                var results = resultsJSON

                        if(results.status == 'success'){

                            $('#logoutBtn').removeClass('off-btn').trigger('classChange');

                            $('.menu-hider').removeClass('menu-active');

                            swup.loadPage({
                                url: '/login', 
                                method: 'GET',
                                customTransition: '' 
                            });

                        }
                        else{

                        }

            })
            .catch(function(err) {
                console.log(err);
            });
        } else {
            menu('menu-offline', 'show', 250);
        } 


    });
    ///////////////////////////////////////////////////////////////////////

// }, 250);