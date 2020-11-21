export default {
    generateColor() {
        var myColor = '';
        return myColor = '#' + (Math.random() * 0xFFFFFF << 0).toString(16);
    },

    swalDelete(title, text){
        let swalOption = {
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonText: "Yes, Delete it !",
            cancelButtonText: "Cancel",
            icon: 'warning',
            cancelButtonColor: '#3085d6',
            confirmButtonColor: '#d33',
            allowOutsideClick: false
        };
        return swalOption;
    },

    swalDeleteOne(title, text){
        let swalOption = {
            title: title,
            text: text,
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes Delete it !",
            confirmButtonColor: '#f56065',
            cancelButtonText: "Cancel",
            closeOnConfirm: true,
            closeOnCancel: true,
            allowOutsideClick: false
        };
        return swalOption;
    },

    dateFormat(date) {
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ];
        let formatedate = new Date(date);
        let hours = formatedate.getHours();
        let minutes = formatedate.getMinutes();
        let ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0' + minutes : minutes;
        let strTime = hours + ':' + minutes + ' ' + ampm;

        let moment = '&emsp;&emsp;' + formatedate.getDate() + ' ' + monthNames[formatedate.getMonth()] + ' ' + formatedate.getFullYear() + '<br>&emsp;&emsp;' + strTime;
        return moment;
    },
}
