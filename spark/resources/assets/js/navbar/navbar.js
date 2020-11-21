module.exports = {

    props: [
        'user', 'teams', 'currentTeam',
        'unreadAnnouncementsCount', 'unreadNotificationsCount'
    ],

    data: function() {
        return {
            totalCommentCount : 0,
            Socket : null
        }

    },


    created() {
        this.commentCount();
    },

    mounted(){
        var _this = this;
        Bus.$on('showComment', function () {
            _this.commentCount();
        });
        Bus.$on('UpdateCommentCount', function () {
            _this.commentCount();
        });
        _this.connectSocket();

    },
    computed:{
        notificationsCount(){
            return this.unreadAnnouncementsCount + this.unreadNotificationsCount;
        }
    },
    methods: {
        connectSocket: function () {
            let app = this;
            if (app.Socket == null) {
                app.Socket = io.connect(window.socket_url);
                app.Socket.on('notification-update', function (res) {
                    setTimeout(function () {
                        app.commentCount();
                    },50)
                })
            }
        },
        commentCount(){
            var _this = this;
            var projectId = _this.$route.params.projectId;
            axios.post('/api/get-all-unseen-comment-count')
                .then(response => response.data)
                .then(response => {
                    _this.totalCommentCount = response.comments;
                })

        },
        /**
          * Show the user's notifications.
          */
         showNotifications(data) {
            if (data === 'notification'){
                Bus.$emit('showNotifications');
            } else {
                Bus.$emit('showComment');
            }

        },


        /**
         * Show the customer support e-mail form.
         */
        showSupportForm() {
            Bus.$emit('showSupportForm');
        }
    }
};
