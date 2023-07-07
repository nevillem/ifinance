<!-- appended styles -->
<link rel="stylesheet" href="./assets/css/styles/dashboard.css">
<link rel="stylesheet" href="./assets/css/styles/popup.css">
<style media="screen">

.select2, .typeahead.tt-input, .typeahead.tt-hint, .select2-container--default .select2-search--dropdown .select2-search__field {
    display: block;
    /*width: 100%;*/
     height: auto;
    padding: 0.469rem 0.8rem !important;
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 1.5;
    color: #000;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    /*appearance: none; */
    border-radius: 0.25rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
.select2-container--focus.select2-container--default .select2-selection--single,
 .select2-container--focus.select2-container--default .select2-selection--multiple {
  /* border: 1px solid #ced4da;*/
    border: none;
}
  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
      color: #fff;
      opacity: 0.5;
  }
</style>
<!-- add member -->
<div class="modal fade" id="addmembermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add New Member</h4>
        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
      </div>
      <div class="modal-body mx-0">
        <div class="row">

        </div>
        <form class="container" id="addmemberform" action="#" method="post">
        <fieldset id="biodata">
          		<!-- <h6 class="w-200 fs-2 text-center"><span class="badge bg-danger">Basic Info</span></h6> -->
              <!-- <hr> -->
            <div class="row">
            <!--<div class="col-md-6">-->
            <!--  <div class="form-group mb-4">-->
            <!--  <select  name="type" id="type" onchange="formFields(this.value)" class="border-1 form-control-md" required>-->
            <!--        <option disabled selected hidden>Account Type</option>-->
            <!--        <option value="individual">Individual</option>-->
            <!--        <option value="group">Group</option>-->
            <!--    </select>-->
            <!--  </div>-->
            <!--  </div>-->
            <div class="col-md-6 form-account-add-single-member">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">First Name</label>
                  <input type="text" name="firstname" id="firstname" placeholder="Enter Firstname *" class="form-control border-0 shadow form-control-md input-text" required>
                </div>
              </div>
              <!-- Select Gender -->
              <div class="col-md-6 form-account-add-single-member" id="gender-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Select Gender</label>
                  <select name="gender" id="gender" class="border-1 form-control-md input-text" required>
                    <option disabled selected hidden>select gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                  </select>
                </div>
              </div>
              <!-- Middlename -->
              <div class="col-md-6 form-account-add-single-member">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Middle Name</label>
                  <input type="text" name="midlename" id="midlename" placeholder="Enter Middlename" class="form-control border-0 shadow form-control-md input-text">
                </div>
              </div>
              <!-- Date Of Birth -->
              <div class="col-md-6 form-account-add-single-member" id="dob-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Select Date of Birth</label>
                  <input type="text" onfocus="(this.type = 'date')" name="dob" id="dob" placeholder="Date of birth *" class="form-control border-0 shadow form-control-md input-text" required>
                </div>
              </div>

              <!-- Lastname -->
              <div class="col-md-6 form-account-add-single-member">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Last Name</label>
                  <input type="text" name="lastname" id="lastname" placeholder="Enter Lastname *" class="form-control border-0 shadow form-control-md input-text" required>
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
              <div class="modal-footer">
                  <a class="btn btn-success next">Next</a>
                </div>
            </fieldset>
            <fieldset id="middledata">
                <div class="row">
                  <!-- Date Of Registration -->
                  <div class="col-md-6">
                    <div class="form-group mb-4 input-container">
                      <label for="" class="form-label">Date Of Registration</label>
                      <input type="text" onfocus="(this.type = 'date')" name="doj" id="doj" placeholder="Date of Registration *" class="form-control border-0 shadow form-control-md input-text" required>
                    </div>
                  </div>

                  <!-- Address -->
                  <div class="col-md-6">
                    <div class="form-group mb-4 input-container">
                      <label for="" class="form-label">Address</label>
                      <input type="text" name="address" id="address" placeholder="Address *" class="form-control border-1 form-control-md input-text" required>
                    </div>
                  </div>
                  <!-- Email Address -->
                  <div class="col-md-6">
                    <div class="form-group mb-4 input-container">
                      <label for="" class="form-label">Email Address</label>
                      <input type="email" name="email" id="email" placeholder="Email Address *" class="form-control border-0 shadow form-control-md input-text">
                    </div>
                  </div>
                  <div class="col-md-6" id="gender-field">
                    <div class="form-group mb-4 input-container">
                      <label class="form-label">Employment Status</label>
                      <select name="employment_status" id="employment_status"  class="border-1 form-control-md input-text" required>
                        <option disabled selected hidden>Employment status</option>
                        <option value="employed">Employed</option>
                        <option value="self-employed">Self Employed</option>
                        <option value="unemployed">Unemployed</option>
                      </select>
                    </div>
                  </div>
                  <!-- Identification Number -->
                  <div class="col-md-6">
                    <div class="form-group mb-4 input-container">
                      <label class="form-label">National Identification Number</label>
                      <input type="text" name="identification" id="identification" placeholder="Identification number *" maxlength="14" class="form-control border-0 shadow form-control-md input-text" required>
                      <input type="hidden" name="id_update" id="id_update">
                    </div>
                  </div>

                  <!-- Gross Income/Salary -->
                  <div class="col-md-6">
                    <div class="form-group mb-4 input-container">
                      <label class="form-label">Estimated Annual Income</label>
                      <input type="text" name="gross_income" id="gross_income"  placeholder="Estimated annual income" class="form-control border-0 shadow form-control-md input-text">
                    </div>
                  </div>


                  </div>
                  <div class="modal-footer">
                    <a class="btn btn-warning mr-auto" id="backtoBio">Previous</a>
                    <a   class="btn btn-success next" >Next</a>
                  </div>
                </fieldset>

          		<fieldset id="contactinfo">
                <!-- <h6 class="w-200 fs-6 text-center"><span class="badge bg-danger">Contact Info</span></h6>
                <hr> -->
                 <div class="row">
                   <!-- Select Marital Status -->
                   <div class="col-md-6 form-account-add-single-member" id="gender-field">
                     <div class="form-group mb-4 input-container">
                       <label class="form-label">Select Marital Status</label>
                       <select name="marital_status" id="marital_status" class="border-1 form-control-md input-text" required>
                         <option disabled selected hidden>select marital status</option>
                         <option value="single">Single</option>
                         <option value="married">Married</option>
                          <option value="widowed">Widowed</option>
                          <option value="separated">Separated</option>
                          <option value="divorced">Divorced</option>
                       </select>
                     </div>
                   </div>

                   <div class="col-md-6">
                     <div class="form-group mb-4 input-container">
                       <label class="form-label">Group</label>
                       <select  id="sacco_group" name="sacco_group" data-placeholder="Select group" multiple=""  class="border-5 form-control-md ">
                         <!-- <option value="">Select Group</option> -->
                         <!-- <option value="1">SACCO</option> -->
                         <!-- <option value="2">SACCO2</option> -->
                       </select>
                     </div>
                   </div>


                   <div class="col-md-6">
                     <div class="form-group mb-4 input-container">
                       <label for="" class="form-label">Comments/Notes</label>
                       <textarea name="attach" id="attach" placeholder="Enter Extra Comments/Notes" rows="4" class="form-control border-1 form-control-md input-text"></textarea>
                     </div>
                   </div>

                   <div class="col-md-6 form-account-add-single-member" id="gender-field">
                     <div class="form-group mb-4 input-container">
                       <label class="form-label">Member Registration Status</label>
                       <select name="status"  id="status" class="border-1 form-control-md input-text" required>
                         <!-- <option value="">Select Member Status</option> -->
                         <option value="active">Active</option>
                         <option value="dormant">Dormant</option>
                         <option value="withdrawn">Withdrawn</option>
                         <option value="suspended">Suspended</option>
                         <option value="deceased">Deceased</option>
                      </select>
                     </div>
                   </div>

              </div>
                <div class="modal-footer">
                  <a class="btn btn-warning mr-auto" id="previous">Previous</a>
                  <button type="submit" class="btn btn-success login">Continue</button>
                  <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
                </div>
          </fieldset>
    </form>
    </div>
  </div>
</div>
</div>

<!-- images -->
<div class="modal fade" id="addmemberimagesmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Upload User Images</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="" id="addmemberimages"  method="post" enctype="multipart/form-data">
              <div class="form-group mb-4">
                <input type="file" name="imagefile"  id="imagefile" class="dropify form-control border-0 shadow form-control-md" multiple required>
              </div>
        <div class="modal-footer  pl-4 ml-5 col-9">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Later</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">Save</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>
</div>


<!-- add member -->
<div class="modal fade" id="editmembermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Edit Member/Account</h4>
        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">X</button>
      </div>
      <div class="modal-body mx-0">
        <form class="container" id="editmemberform" action="#" method="post">
        <fieldset id="biodata_update">
          		<!-- <h6 class="w-200 fs-2 text-center"><span class="badge bg-danger">Basic Info</span></h6>
              <hr> -->
            <div class="row">
              <div class="col-md-6 form-account-add-single-member">
                  <div class="form-group mb-4 input-container">
                    <label class="form-label">First Name</label>
                    <input type="text" name="firstname" id="firstname_update" placeholder="Enter Firstname *" class="form-control border-0 shadow form-control-md input-text" required>
                  </div>
                </div>
                <!-- Middlename -->
                <div class="col-md-6 form-account-add-single-member">
                  <div class="form-group mb-4 input-container">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="midlename" id="midlename_update" placeholder="Enter Middlename" class="form-control border-0 shadow form-control-md input-text">
                  </div>
                </div>
              <div class="col-md-6 form-account-add-single-member">
              <div class="form-group mb-4 input-container">
                <label class="form-label">Last Name</label>
                <input type="text" name="lastname" id="lastname_update" class="form-control border-0 shadow form-control-md input-text" required>
              </div>
              </div>

            <!-- Select Gender -->
            <div class="col-md-6 form-account-add-single-member" id="gender-field">
              <div class="form-group mb-4 input-container">
                <label class="form-label">Select Gender</label>
                <select name="gender" id="gender_update" class="border-1 form-control-md input-text" required>
                  <option disabled selected hidden>select gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
              </div>
            </div>
            <div class="col-md-6 form-account-add-single-member" id="dob-field">
              <div class="form-group mb-4 input-container">
                <label class="form-label">Select Date of Birth</label>
                <input type="text" onfocus="(this.type = 'date')" name="dob" id="dob_update" placeholder="Date of birth *" class="form-control border-0 shadow form-control-md input-text" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-4 input-container">
                <label class="form-label">National Identification Number</label>
                <input type="text" name="identification" id="identification_update" placeholder="Identification number *" maxlength="14" class="form-control border-0 shadow form-control-md input-text">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-4 input-container">
                <label for="" class="form-label">Phone number</label>
                <input type="number" name="contact" id="contact_update"  class="form-control border-0 shadow form-control-md input-text" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-4 input-container">
                <label for="" class="form-label">Email Address</label>
                <input type="email" name="email" id="email_update"  class="form-control border-0 shadow form-control-md input-text">
              </div>
            </div>
              </div>
              <div class="modal-footer">
                  <a class="btn btn-success next_one">Next</a>
                </div>
            </fieldset>

          		<fieldset id="contactinfo_update">
                 <div class="row">
                   <div class="col-md-6" id="gender-field">
                     <div class="form-group mb-4 input-container">
                       <label class="form-label">Employment Status</label>
                       <select name="employment_status" id="employment_status_update"  class="border-1 form-control-md input-text" required>
                         <option disabled selected hidden>Employment status</option>
                         <option value="employed">Employed</option>
                         <option value="self-employed">Self Employed</option>
                         <option value="unemployed">Unemployed</option>
                       </select>
                     </div>
                   </div>
                   <!-- Gross Income/Salary -->
                   <div class="col-md-6">
                     <div class="form-group mb-4 input-container">
                       <label class="form-label">Estimated Annual Income</label>
                       <input type="text" name="gross_income" id="gross_income"  placeholder="Estimated annual income" class="form-control border-0 shadow form-control-md input-text">
                     </div>
                   </div>

              <div class="col-md-6">
              <div class="form-group mb-4 input-container">
                <label for="" class="form-label">Date Registered</label>
              <input type="text" onfocus="(this.type='date')" name="doj" id="doj_update" class="form-control border-0 shadow form-control-md input-text" required>
              </div>
              </div>
              <div class="col-md-6 form-account-add-single-member" id="gender-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Member Registration Status</label>
                  <select name="status"  id="status_update" class="border-1 form-control-md input-text" required>
                    <option value="active">Active</option>
                    <option value="dormant">Dormant</option>
                    <option value="withdrawn">Withdrawn</option>
                    <option value="suspended">Suspended</option>
                    <option value="deceased">Deceased</option>

                  </select>
                </div>
              </div>

              <div class="col-md-6">
              <div class="form-group mb-4 input-container">
                <label for="" class="form-label">Address</label>
              <textarea  name="address" id="address_update" rows="4" class="form-control border-1 form-control-md" required></textarea>
              </div>
              </div>
              <div class="col-md-6 form-account-add-single-member" id="gender-field">
                <div class="form-group mb-4 input-container">
                  <label class="form-label">Select Marital Status</label>
                  <select name="marital_status" id="marital_status_update" class="border-1 form-control-md input-text" required>
                    <option disabled selected hidden>select marital status</option>
                    <option value="single">Single</option>
                    <option value="married">Married</option>
                     <option value="widowed">Widowed</option>
                     <option value="separated">Separated</option>
                     <option value="divorced">Divorced</option>
                  </select>
                </div>
              </div>
              </div>
                <div class="modal-footer">
                  <a class="btn btn-warning" id="previous_one">Previous</a>
                  <button type="submit" class="btn btn-success login">Continue</button>
                  <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
                </div>
          </fieldset>
    </form>
    </div>
  </div>
</div>
</div>
<style>
.modal-scroll{
  height: 500px;
  overflow-y: auto;
}
</style>
<div class="modal fade" id="accountstatementmodal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="myModalLabel"
  aria-hidden="true">
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
            <div class="card container">

                <div class="card-body p-0" id="statement-print">
                    <div class="row p-3">
                        <div class="col-md-4" id="saccologo">
                          </div>
                        <div class="col-md-4 text-left">
                            <p class="font-weight-bold mb-1">Account Statement</p>
                            <p class="text-muted">From: <span id="startdate"></span></p>
                            <p class="text-muted">To:  <span id="enddate"></span> </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <p class="font-weight-bold mb-1" id="sacconame_statememt">Sacco Name</p>
                            <p class="text-muted" id="saccocontact_statement">Sacco Contact</p>
                            <p class="text-muted" id="saccoemail_statement">Sacco Email</p>
                            <p class="text-muted" id="saccoaddress_statement">Sacco Address</p>
                        </div>
                    </div>

                    <hr class="my-0">

                    <div class="row pb-2 p-2">
                        <div class="col-md-6">
                            <p class="font-weight-bold mb-2">Account Info.</p>
                            <p class="" id="accountname"></p>
                            <p class="" id="accountnumber"></p>
                            <p class="" id="accountcontact"></p>
                            <p class="" id="accountaddress"></p>
                        </div>

                        <div class="col-md-6 text-right">
                            <p class="font-weight-bold mb-4">Account Summary</p>
                            <!-- <p class="mb-1"><span class="text-muted">Withdraws: </span> 1425782</p>
                            <p class="mb-1"><span class="text-muted">Deposits: </span> 10253642</p> -->
                            <p class="mb-1"><span class="text-muted"> Current Account Balance: </span> <br> <span id="accountbalance"></span> </p>
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
        <div class="modal-footer pl-4 ml-5 col-7">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary login" onClick="generatePDF()">Download</button>
          <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
        </div>
      </div>
    </div>

    </div>
  </div>
</div>
</div>
<!-- date range -->
<div class="modal fade" id="rangemembermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Select Date Range</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="needs-validation row" id="accountstatement"  method="post" novalidate>
          <div class="form-group mb-4 col-6">
            <input type="text" onfocus="(this.type='date')" name="mindate"  id="mindate" placeholder="Enter Min Date" class="form-control border-0 shadow form-control-md" required>
          </div>
          <div class="form-group mb-4 col-6">
            <input type="hidden" name="memberid" id="member_statement_id">
            <input type="text" onfocus="(this.type='date')" name="maxdate"  id="maxdate" placeholder="Enter Max Date" class="form-control border-0 shadow form-control-md" required>
          </div>
        <div class="modal-footer  pl-4 ml-5 col-8">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success login" >Generate</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>

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
