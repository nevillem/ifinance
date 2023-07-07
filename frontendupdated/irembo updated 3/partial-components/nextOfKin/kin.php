<!-- appended styles -->
<link rel="stylesheet" href="./assets/css/styles/dashboard.css">
<link rel="stylesheet" href="./assets/css/styles/popup.css">

<!-- Add Member -->
<div class="modal fade" id="addnextofkinmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width:80%;" role="document">
        <div class="modal-content">
            <!-- Modal Heading -->
            <div class="popup-heading">Add Next Of Kin<i class="fa-solid fa-xmark close-popup" data-dismiss="modal"></i></div>
            <!-- Modal Form -->
            <div class="modal-body mx-0">
                <form class="container" id="addnextofkinform" action="#" method="post">

                    <!-- Added fields -->
                    <!-- Middle Name -->
                    <!-- Fieldset 1 -->
                    <fieldset id="biodata">
                        <!-- Form row -->
                        <div class="row">
                            <!-- Select Gender -->
                            <div class="col-md-6 form-account-add-single-member" id="gender-field">
                                <div class="form-group mb-4 input-container">
                                    <label class="form-label">Select SACCO Member*</label>
                                    <select name="memberid" id="memberid" class="border-1 form-control-md input-text member-select-input" required>
                                        <option value="">Select SACCO Member</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Firstname -->
                            <div class="col-md-6 form-account-add-single-member">
                                <div class="form-group mb-4 input-container">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="firstname" id="firstname" placeholder="Enter Firstname *" class="form-control border-0 shadow form-control-md input-text" required>
                                </div>
                            </div>

                            <!-- Middlename -->
                            <div class="col-md-6 form-account-add-single-member">
                                <div class="form-group mb-4 input-container">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" name="midlename" id="midlename" placeholder="Enter Middlename" class="form-control border-0 shadow form-control-md input-text">
                                </div>
                            </div>

                            <!-- Lastname -->
                            <div class="col-md-6 form-account-add-single-member">
                                <div class="form-group mb-4 input-container">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="lastname" id="lastname" placeholder="Enter Lastname *" class="form-control border-0 shadow form-control-md input-text" required>
                                </div>
                            </div>

                            <!-- Date Of Birth -->
                            <div class="col-md-6 form-account-add-single-member" id="dob-field">
                                <div class="form-group mb-4 input-container">
                                    <label class="form-label">Select Date of Birth</label>
                                    <input type="text" onfocus="(this.type = 'date')" name="dob" id="dob" placeholder="Date of birth *" class="form-control border-0 shadow form-control-md input-text">
                                </div>
                            </div>

                            <!-- Phone Number -->
                            <div class="col-md-6 form-account-add-single-member">
                                <div class="form-group mb-4 input-container">
                                    <label for="" class="form-label">Phone Number</label>
                                    <input type="text" name="contact" id="contact" placeholder="Contact phone *" maxlength="10" class="form-control border-0 shadow form-control-md input-text" required>
                                </div>
                            </div>
                        </div>
                        <!-- ^^^ End of fieldset 1 row -->

                        <!-- Next button -->
                        <div class="modal-footer">
                          <a class="btn btn-success next">Next</a>
                        </div>
                    </fieldset>

                    <!-- Fieldset 2 -->
                    <fieldset id="contactinfo">
                        <!-- <h6 class="w-200 fs-6 text-center"><span class="badge bg-danger">Contact Info</span></h6> -->
                        <!-- <hr> -->
                        <div class="row">

                            <!-- Relationship -->
                            <div class="col-md-6">
                                <div class="form-group mb-4 input-container">
                                    <label for="" class="form-label">Relationship*</label>
                                    <input type="text" name="relationship" id="relationship" placeholder="Enter Relationship *" class="form-control border-1 form-control-md input-text" required>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="col-md-6">
                                <div class="form-group mb-4 input-container">
                                    <label for="" class="form-label">Address</label>
                                    <input type="text" name="address" id="address" placeholder="Enter Town/City *" class="form-control border-1 form-control-md input-text">
                                </div>
                            </div>

                            <!-- Date Of Registration -->
                            <div class="col-md-6">
                                <div class="form-group mb-4 input-container">
                                    <label for="" class="form-label">Date Of Registration</label>
                                    <input type="text" onfocus="(this.type = 'date')" name="doj" id="doj" placeholder="Date of Registration *" class="form-control border-0 shadow form-control-md input-text">
                                </div>
                            </div>

                            <!-- Email Address -->
                            <div class="col-md-6">
                                <div class="form-group mb-4 input-container">
                                    <label for="" class="form-label">Email Address</label>
                                    <input type="email" name="email" id="email" placeholder="Email Address *" class="form-control border-0 shadow form-control-md input-text">
                                </div>
                            </div>

                            <!-- Identification Number -->
                            <div class="col-md-6">
                                <div class="form-group mb-4 input-container">
                                    <label class="form-label">National Identification Number</label>
                                    <input type="text" name="identification" id="identification" placeholder="Enter NIN " maxlength="14" class="form-control border-0 shadow form-control-md input-text">
                                </div>
                            </div>

                            <!-- Inheritence -->
                            <div class="col-md-6">
                                <div class="form-group mb-4 input-container">
                                    <label class="form-label">Inheritence (%)*</label>
                                    <input type="text" name="inheritance" id="inheritance" placeholder="Enter Inheritence" class="form-control border-0 shadow form-control-md input-text">
                                </div>
                            </div>
                        </div>
                        <!-- ^^^ End of fieldset 2 row -->

                        <!-- Back & Next Button -->
                        <div class="modal-footer">
                            <!-- Back Button -->
                            <a class="btn btn-warning mr-auto" id="previous">Previous</a>
                            <button type="submit" class="btn btn-success login">Submit</button>

                            <!-- Loader -->
                            <div class="loading p-2 col-xs-1" align="center">
                                <div class="loader"></div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Images -->
    <div class="modal fade" id="addmemberimagesmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" style="width:80%;" role="document">
            <div class="modal-content">
                <div class="modal-header text-center text-white bg-warning">
                    <h4 class="modal-title h5 w-100 font-weight-bold">Upload User Images</h4>
                </div>
                <div class="modal-body mx-0">
                    <div class="alert" role="alert"></div>
                    <form class="" id="addmemberimages" method="post" enctype="multipart/form-data">
                        <div class="form-group mb-4">
                            <input type="file" name="imagefile" id="imagefile" class="dropify form-control border-0 shadow form-control-md" multiple required>
                        </div>
                        <div class="modal-footer  pl-4 ml-5 col-9">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Later</button>
                            <button type="reset" class="btn btn-warning">Reset</button>
                            <button type="submit" class="btn btn-success login">Save</button>
                            <div class="loading p-2 col-xs-1" align="center">
                                <div class="loader"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Member -->
        <div class="modal fade" id="editmembermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" style="width:80%;" role="document">
                <div class="modal-content">
                    <!-- Modal Heading -->
                    <div class="popup-heading">Edit Member (Account)<i class="fa-solid fa-xmark close-popup" data-dismiss="modal"></i></div>
                    <!-- Modal Form -->
                    <div class="modal-body mx-0">
                        <form class="container" id="editmemberform" action="#" method="post">
                            <fieldset id="biodata_update">
                                <!-- Edit form row -->
                                <div class="row">

                                    <!-- Edit Firstname -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-4 input-container">
                                            <label for="" class="form-label">Firstname</label>
                                            <input type="text" name="firstname" id="firstname_update" class="form-control border-0 shadow form-control-md input-text" required>
                                        </div>
                                    </div>

                                    <!-- Edit Middlename -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-4 input-container">
                                            <label for="" class="form-label">Middlename</label>
                                            <input type="text" name="middlename" id="middlename_update" class="form-control border-0 shadow form-control-md input-text" required>
                                        </div>
                                    </div>

                                    <!-- Edit Lastname -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-4 input-container">
                                            <label for="" class="form-label">Lastname</label>
                                            <input type="text" name="lastname" id="lastname_update" class="form-control border-0 shadow form-control-md input-text" required>
                                        </div>
                                    </div>

                                    <!-- Edit Gender -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-4 input-container">
                                            <label for="" class="form-label">Gender</label>
                                            <select name="gender" id="gender_update" class="border-1 form-control-md input-text" required>
                                                <option disabled selected hidden>select gender</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Edit Date Of Birth -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-4 input-container">
                                            <label for="" class="form-label">Date Of Birth</label>
                                            <input type="text" onfocus="(this.type = 'date')" name="dob" id="dob_update" class="form-control border-0 shadow form-control-md input-text" required>
                                        </div>
                                    </div>

                                    <!-- Edit Identification Number -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-4 input-container">
                                            <label for="" class="form-label">National Identification Number</label>
                                            <input type="text" name="identification" id="identification_update" placeholder="Identification number *" maxlength="14" class="form-control border-0 shadow form-control-md input-text" required>
                                            <input type="hidden" name="id_update" id="id_update">
                                        </div>
                                    </div>
                                </div>
                                <!-- End of fieldset 1: Edit -->

                                <!-- Next Button: Edit form -->
                                <div class="modal-footer">
                                    <div class="button-container">
                                        <button class="right-button-container next_one" type="button">
                                            <div class="right-button-text">Next</div>
                                        </button>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset id="contactinfo_update">
                                <div class="row">

                                    <!-- Edit Phone Number -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-4 input-container">
                                            <label for="" class="form-label">Phone Number</label>
                                            <input type="number" name="contact" id="contact_update" maxlength="10" class="form-control border-0 shadow form-control-md input-text" required>
                                        </div>
                                    </div>

                                    <!-- Edit Email -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-4 input-container">
                                            <label for="" class="form-label">Email</label>
                                            <input type="email" name="email" id="email_update" class="form-control border-0 shadow form-control-md input-text">
                                        </div>
                                    </div>

                                    <!-- Edit Date Of Registration -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-4 input-container">
                                            <label for="" class="form-label">Date Of Registration</label>
                                            <input type="text" onfocus="(this.type = 'date')" name="doj" id="doj_update" class="form-control border-0 shadow form-control-md input-text" required>
                                        </div>
                                    </div>

                                    <!-- Edit Registration Status -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-4 input-container">
                                            <label for="" class="form-label">Registration Status</label>
                                            <select name="status" id="status_update" class="border-1 form-control-md input-text" required>
                                                <option disabled selected hidden>select registration status</option>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Edit Address -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-4 input-container">
                                            <label for="" class="form-label">Address</label>
                                            <textarea name="address" id="address_update" rows="4" class="form-control border-1 form-control-md input-text" required></textarea>
                                        </div>
                                    </div>

                                    <!-- Edit: Attachment -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-4 input-container">
                                            <label for="" class="form-label">Attach Extra Info</label>
                                            <textarea name="attach" id="attach_update" rows="4" class="form-control border-1 form-control-md input-text"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <!-- End of fieldset 2 row: Edit -->

                                <!-- Back & Next Button: Edit -->
                                <div class="modal-footer">
                                    <!-- Back Button -->
                                    <div class="two-button-container">
                                        <button class="right-button-container" type="button" id="previous_one">
                                            <div class="right-button-text">Back</div>
                                        </button>
                                        <!-- Submit button -->
                                        <button class="right-button-container next" type="button">
                                            <div class="right-button-text">Continue</div>
                                        </button>
                                    </div>
                                    <!-- Loader -->
                                    <div class="loading p-2 col-xs-1" align="center">
                                        <div class="loader"></div>
                                    </div>


                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
            <style>
                .modal-scroll {
                    height: 500px;
                    overflow-y: auto;
                }

                #statement-print {
                    color: #000;
                }
            </style>
            <div class="modal fade" id="accountstatementmodal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable" style="width:80%;" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center text-white bg-warning">
                            <h4 class="modal-title h5 w-100 font-weight-bold">Account Statement</h4>
                            <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
                        </div>
                        <div class="modal-body mx-0 modal-scroll">
                            <div class="container-fuild">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="">

                                            <div class=" p-0" id="statement-print">
                                                <!-- <div class="row p-3">
                                        <div class="col-md-4" id="saccologo">
                                          </div>
                                        <div class="col-md-4 text-left">
                                            <p class="font-weight-bold mb-1">Account Statement</p>
                                            <p class="text-dark">From: <span id="startdate"></span></p>
                                            <p class="text-dark">To:  <span id="enddate"></span> </p>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <p class="font-weight-bold mb-1" id="sacconame_statememt">Sacco Name</p>
                                            <p class="text-dark" id="saccocontact_statement">Sacco Contact</p>
                                            <p class="text-dark" id="saccoemail_statement">Sacco Email</p>
                                            <p class="text-dark" id="saccoaddress_statement">Sacco Address</p>
                                        </div>
                                    </div> -->

                                                <!-- <hr class="my-0"> -->

                                                <div class="row pb-0 p-0">
                                                    <div class="col-md-6">
                                                        <p class="font-weight-bold mb-0">Account Info.</p>
                                                        <span id="accountname"></span>
                                                        <span id="accountnumber"></span>
                                                        <!-- <p class="" id="accountcontact"></p>
                                                <p class="" id="accountaddress"></p> -->
                                                    </div>

                                                    <div class="col-md-6 text-right">
                                                        <p class="font-weight-bold mb-0">Account Summary</p>
                                                        <!-- <p class="mb-1"><span class="text-dark">Withdraws: </span> 1425782</p>
                                                <p class="mb-1"><span class="text-dark">Deposits: </span> 10253642</p> -->
                                                        <p class="mb-1"><span class="text-dark"> Current Account Balance: </span> <br> <span id="accountbalance"></span> </p>
                                                    </div>
                                                </div>

                                                <div class="row p-2">
                                                    <div class="col-md-12">
                                                        <table class="table tablesorter" id="statement-table-sorter">
                                                            <thead>
                                                                <tr class="sorter-header">
                                                                    <th title="sort by date" class="border-0 text-uppercase small font-weight-bold is-date" data-sorter="shortDate" data-date-format="yyyy MM dd HH:mm:ss">Date</th>
                                                                    <th class="border-0 text-uppercase small font-weight-bold">Details</th>
                                                                    <th class="border-0 text-uppercase small font-weight-bold">Withdraws</th>
                                                                    <th class="border-0 text-uppercase small font-weight-bold">Deposits</th>
                                                                    <th class="border-0 text-uppercase small font-weight-bold">Method</th>
                                                                    <th class="border-0 text-uppercase small font-weight-bold">Reference</th>
                                                                    <th class="border-0 text-uppercase small font-weight-bold">Charges</th>
                                                                    <th class="border-0 text-uppercase small font-weight-bold">Balance</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="deposit_transaction">

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-row-reverse text-dark p-4">

                                                    <div class="py-3 px-2 text-right">
                                                        <div class="mb-2">Balance in Words</div>
                                                        <div class="h6 font-weight-light" id="accountbottomword">null</div>
                                                    </div>

                                                    <div class="py-3 px-2 text-right">
                                                        <div class="mb-2">Balance in Figures</div>
                                                        <div class="h6 font-weight-light" id="accountbottom">0</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer pl-4 ml-4 col-8">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary login" onClick="generatePDF()">Download</button>
                                        <div class="loading p-2 col-xs-1" align="center">
                                            <div class="loader"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- date range -->
                <div class="modal fade" id="rangemembermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-md" role="document">
                        <div class="modal-content">
                            <div class="modal-header text-center text-white bg-warning">
                                <h4 class="modal-title h5 w-100 font-weight-bold">Select Date Range</h4>
                            </div>
                            <div class="modal-body mx-0">
                                <div class="alert" role="alert"></div>
                                <form class="needs-validation row" id="accountstatement" method="post" novalidate>
                                    <div class="form-group mb-4 col-6">
                                        <input type="text" onfocus="(this.type = 'date')" name="mindate" id="mindate" placeholder="Enter Min Date" class="form-control border-0 shadow form-control-md" required>
                                    </div>
                                    <div class="form-group mb-4 col-6">
                                        <input type="hidden" name="memberid" id="member_statement_id">
                                        <input type="text" onfocus="(this.type = 'date')" name="maxdate" id="maxdate" placeholder="Enter Max Date" class="form-control border-0 shadow form-control-md" required>
                                    </div>
                                    <div class="modal-footer  pl-4 ml-5 col-8">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-success login">Generate</button>
                                        <div class="loading p-2 col-xs-1" align="center">
                                            <div class="loader"></div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <script>
                        function formFields(value) {
                            //console.log(value);
                            //$('.form-account-add-single-member').fadeIn();
                            // if (value == 'individual') {
                            //     $('#firstname').attr('placeholder', 'firstname *');
                            //     $('#lastname').attr('placeholder', 'lastname *');
                            //     $('#identification').attr('placeholder', 'Identification number * *');

                            // }
                            // if (value == 'group') {
                            //     $('.form-account-add-single-member').fadeIn();
                            //     $('#gender-field').fadeOut();
                            //     $('#dob-field').fadeOut();
                            //     $('#firstname').attr('placeholder', 'group name *');
                            //     $('#lastname').attr('placeholder', 'Account holder name *');

                            // }
                        }
                    </script>
                    <script>
                        function generatePDF() {
                            const {
                                jsPDF
                            } = window.jspdf;

                            var doc = new jsPDF('l', 'pt', [751.89, 795.28]);
                            var pdfjs = document.querySelector('#statement-print');
                            var randomnumber = Math.floor(Math.random() * 1000000000000);
                            doc.html(pdfjs, {
                                callback: function(doc) {
                                    doc.save('' + randomnumber + '-statement.pdf');
                                },
                                x: 10,
                                y: 10
                            });
                        }
                    </script>
