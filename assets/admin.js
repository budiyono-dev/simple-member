jQuery(document).ready(function($) {
    console.log("ready");
    const firstData = $('#memberTable tbody tr:eq(0)').find('td').html();
    if (firstData !== 'No Data') {
        $('#memberTable').DataTable({
            "order": [],
            "columnDefs": [
                { "orderable": false, "targets": [0, 4] } 
            ]
        });
    }

    $('#formDeleteMember').submit(function(event) {
        event.preventDefault(); 

        if (confirm("Are you sure you want to delete this member?")) {
            event.target.submit()
        } else {
            alert("Delete action cancelled.");
        }
    });
});
