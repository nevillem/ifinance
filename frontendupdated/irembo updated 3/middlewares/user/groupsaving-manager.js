$(document).ready(function(){

   // get group savings
                get_groupsavings()
                async function get_groupsavings() {
                    $.ajax({
                        url: base+"depositgroup-manager",
                        headers: {
                            'Authorization': localStorage.token,
                            'Content-Type': 'application/json'
                        },
                        type: "GET",
                        success: function(response) {
                            var nums = response.data.transactions.length;
                            $('#numofsavings').html(nums);
                            var no = 0
                            for (var i = 0; i < nums; i++) {
                                no++
                                var group_savings = '';
                                group_savings += '<tr>';
                                group_savings += '<td> </td>';
                                group_savings += '<td>' +no+ '</td>';
                                group_savings += '<td>' +response.data.transactions[i].account+ '</td>';
                                group_savings += '<td>' +response.data.transactions[i].firstname+ '</td>';
                                group_savings += '<td>' +response.data.transactions[i].amount+ '</td>';
                                group_savings += '<td>' +response.data.transactions[i].method+ '</td>';
                                group_savings += '<td>' +response.data.transactions[i].timestamp+ '</td>';
                                group_savings += '<td>' +response.data.transactions[i].status+ '</td>';
                                group_savings += '</tr>';

                                $("#groupsavings_table").append(group_savings);
                            }

                            $('#dataTables-groupsavings').DataTable({
                                responsive: false,
                                processing: true,
                                serverSide: false,
                                retrieve: true,
                                autoWidth: true,
                                paging: true,
                                dom: 'lBfrtip',
                                ordering: true,
                                info: true,
                                select: true,
                                keys: true,
                                autoFill: true,
                                colReorder: true,
                                pageLength: 10,
                                buttons: [
                                    { extend: 'copy', className: 'btn btn-success btn-sm mdi mdi-content-copy', exportOptions: { columns: [1, ':visible'] } },
                                    { extend: 'csv', className: 'btn btn-danger btn-sm mdi mdi-file-excel', exportOptions: { columns: [1, ':visible'] } },
                                    { extend: 'excel', className: 'btn btn-dark btn-sm mdi mdi-file-excel-box', exportOptions: { columns: [1, ':visible'] } },
                                    { extend: 'pdf', className: 'btn btn-info btn-sm mdi mdi-file-pdf', exportOptions: { columns: [1, ':visible'] } },
                                    { extend: 'print', className: 'btn btn-warning btn-sm mdi mdi-printer', exportOptions: { columns: [1, ':visible'] } },
                                    { extend: 'colvis', className: 'btn btn-sm btn-white' }
                                    // 'pdf',  'excel', 'csv', 'print', 'copy',
                                ],
                                "lengthMenu": [
                                    [5, 10, 25, 50, -1],
                                    [5, 10, 25, 50, "All"]
                                ],
                                language: {
                                    "emptyTable": "There Is No Data",
                                    "zeroRecords": "No Data That Matches Your Search Query",
                                    searchPlaceholder: "Search Data",
                                    search: "Filter Records"
                                },
                                columnDefs: [{
                                    orderable: false,
                                    className: 'select-checkbox',
                                    targets: 0
                                }],
                                select: {
                                    style: 'multi',
                                    selector: 'td:first-child'

                                }
                                ,
                                fixedHeader: {
                                    header: true,
                                    footer: true
                                }
                            });


                        },
                        error: function(xhr, status, error) {
                            if (xhr.status == '401') {
                                get_groupsavings()
                            }
                        }
                    })
                }

            });
