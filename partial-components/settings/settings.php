<!-- add branch -->
<div class="modal fade" id="addaccount" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add New Account Type</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="needs-validation" id="accounttypeform"  method="post" novalidate>
              <div class="form-group mb-4">
                <input type="text" name="name" id="name" placeholder="account type name *" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>

              <div class="form-group mb-4">
                <input type="number" name="charge" id="charge" placeholder="Account withdraw charge e.g. 1000 *" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
            <div class="form-group mb-4">
                <input type="number" name="balance" id="balance" placeholder="Account minimum balance e.g. 5000 *" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
             <div class="form-group mb-4">
                <textarea name="describe" id="describe" type="text" placeholder="Account type description *" class="form-control border-0 shadow form-control-md" required></textarea>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>

        <div class="modal-footer d-inline-flex pl-4 ml-5">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">submit</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>

<!-- edit model -->
<div class="modal fade" id="editaccountmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" style="width:70%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Edit Account Types</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="needs-validation" id="accounttypeformedit"  method="post" novalidate>
              <div class="form-group mb-4">
                <input type="text" name="name" id="name_update" class="form-control border-0 shadow form-control-md" required>
                <input type="hidden" name="id_update" id="id_update">
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>

              <div class="form-group mb-4">
                <input type="number" name="charge" id="charge_update" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
            <div class="form-group mb-4">
                <input type="number" name="balance" id="balance_update" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
             <div class="form-group mb-4">
                <textarea name="describe" id="describe_update" type="text" class="form-control border-0 shadow form-control-md" required></textarea>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>

        <div class="modal-footer d-inline-flex pl-4 ml-5">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">Update</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>

<!-- share settings -->
<!-- add share type -->
<div class="modal fade" id="addshare" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add New Share Type</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="needs-validation" id="addshareform"  method="post" novalidate>
              <div class="form-group mb-4">
                <input type="text" name="name" id="name" placeholder="Share type name *" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>

              <div class="form-group mb-4">
                <input type="number" name="price" id="price" placeholder="Price per share e.g. 1000 *" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
            <div class="form-group mb-4">
                <input type="number" name="limit" id="limit" placeholder="Maximum shares e.g. 10 *" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
        <div class="modal-footer d-inline-flex pl-4 ml-5">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">submit</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>

<!-- edit model -->
<div class="modal fade" id="editsharemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" style="width:70%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Edit Share Type</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="needs-validation" id="editshareform"  method="post" novalidate>
              <div class="form-group mb-4">
                <input type="text" name="name" id="name_share" class="form-control border-0 shadow form-control-md" required>
                <input type="hidden" name="id_update" id="id_share">
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>

              <div class="form-group mb-4">
                <input type="number" name="price" id="price_share" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
            <div class="form-group mb-4">
                <input type="number" name="limit" id="limit_share" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>

        <div class="modal-footer d-inline-flex pl-4 ml-5">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">Update</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>

<!-- add loan type -->
<div class="modal fade" id="addloanmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add New Loan Type</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row needs-validation" id="addloanform"  method="post" novalidate>
          <div class="col-6">
            <div class="form-group mb-4">
                <input type="text" name="name" id="name" placeholder="loan type name *" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>

              <div class="form-group mb-4">
                <input type="number" name="interest" id="interest" placeholder="Interest rate e.g. 1.5 *" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
            <div class="form-group mb-4">
                <input type="number" name="penalty" id="penalty" placeholder="Loan penalty rate e.g. 0.5 *" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>
        </div>
        <div class="col-6">
            <div class="form-group mb-4">
                <select  name="frequency" id="frequency" class="form-control border-1 form-control-lg" required>
                    <option disabled selected hidden>select frequency</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="onetime">Onetime</option>
                </select>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
            <div class="form-group mb-4">
                <input type="number" name="fee" id="fee" placeholder="Loan processing fee *" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>
              <div class="form-group mb-4">
                <input type="number" name="period" id="period" placeholder="Loan period e.g. 2 * " class="form-control border-0 shadow form-control-md">
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>
        </div>
        <div class="col-12">
        <div class="form-group mb-4">
                <textarea type="text" name="notes" id="notes" placeholder="Notes" class="form-control border-0 shadow form-control-md"></textarea>
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>
        </div>
        <div class="modal-footer d-inline-flex pl-4 ml-5 col-8">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">submit</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>

<!-- edit model -->
<div class="modal fade" id="editloanmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Edit Loan Type</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row needs-validation" id="editloanform"  method="post" novalidate>
          <div class="col-6">
            <div class="form-group mb-4">
                <input type="text" name="name" id="name_loan" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
                <input type="hidden" name="id_loan" id="id_loan">
              </div>

              <div class="form-group mb-4">
                <input type="number" name="interest" id="interest_loan" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
            <div class="form-group mb-4">
                <input type="number" name="penalty" id="penalty_loan" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>
        </div>
        <div class="col-6">
            <div class="form-group mb-4">
                <select  name="frequency" id="frequency_loan" class="form-control border-1 form-control-lg" required>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="onetime">Onetime</option>
                </select>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
            <div class="form-group mb-4">
                <input type="number" name="fee" id="fee_loan" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>
              <div class="form-group mb-4">
                <input type="number" name="period" id="period_loan" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>
        </div>
        <div class="col-12">
        <div class="form-group mb-4">
                <textarea type="text" name="notes" id="notes_loan" class="form-control border-0 shadow form-control-md"></textarea>
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>
        </div>
        <div class="modal-footer d-inline-flex pl-4 ml-5 col-8">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">Update</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>

<!-- add capital -->
<div class="modal fade" id="addcapitalmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" style="width:60%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add Capital</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="needs-validation" id="addcapitalform"  method="post" novalidate>
            <div class="form-group mb-4">
                <input type="text" name="name" id="name" placeholder="Capital name *" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
              </div>

              <div class="form-group mb-4">
                <input type="number" name="amount" id="amount" placeholder="Amount e.g. 10000000 *" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>

            <div class="form-group mb-4">
                <input type="text" onfocus="(this.type='date')" name="date" id="date" placeholder="Date of capital gain *" class="form-control border-0 shadow form-control-md" required>
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
        <div class="modal-footer d-inline-flex pl-4 ml-5 col-9">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">Save</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>

<!-- add income type -->
<div class="modal fade" id="addincomemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add Income type</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row needs-validation" id="addincomeform"  method="post" novalidate>

        <div class="col-12">
            <div class="form-group mb-4">
                <input type="text" name="name" id="name" placeholder="Income type *" class="form-control border-0 shadow form-control-md" required>
                <input type="hidden" name="type" value="income">
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
        </div>

        <div class="modal-footer d-inline-flex pl-4 ml-5 col-9">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">Save</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>
<!-- edit income type -->
<div class="modal fade" id="editincomemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add Income type</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row needs-validation" id="editincomeform"  method="post" novalidate>

        <div class="col-12">
            <div class="form-group mb-4">
                <input type="text" name="name" id="name_income" class="form-control border-0 shadow form-control-md" required>
                <input type="hidden" name="id_income" id="id_income">
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
        </div>

        <div class="modal-footer d-inline-flex pl-4 ml-5 col-9">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">Update</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>
<!-- add expense type -->
<div class="modal fade" id="addexpensemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" style="width:80%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-warning">
        <h4 class="modal-title h5 w-100 font-weight-bold">Add Expense type</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row needs-validation" id="addexpenseform"  method="post" novalidate>

        <div class="col-12">
            <div class="form-group mb-4">
                <input type="text" name="name" id="name" placeholder="Expense type *" class="form-control border-0 shadow form-control-md" required>
                <input type="hidden" name="type" value="expense">
                <div class="invalid-feedback text-center">please this field is required</div>
            </div>
        </div>

        <div class="modal-footer d-inline-flex pl-4 ml-5 col-9">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success login">Save</button>
        <div class="loading p-2 col-xs-1" align="center"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>

<!-- payment method -->
<div class="modal fade row" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-sm" style="width:70%;" role="document">
    <div class="modal-content">
      <div class="modal-header text-center text-white bg-danger">
        <h4 class="modal-title h5 w-100 font-weight-bold">Delete Payment Mode!</h4>
      </div>
      <div class="modal-body mx-0">
        <div class="alert" role="alert"></div>
        <form class="row" id="deletepayform" action="#" method="post">
          <div class="form-group h4 font-weight-bold text-primary col text-center">

            <label class="text warning text-label">Are You Sure?</label>
            <input type="hidden" name="id" id="deletepayid" class="form-control border-0 shadow form-control-md input-text" required>
          </div>

        <div class="col-md-10 modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success login">Yes</button>
        <div class="load_ing p-2 col-xs-1" align="center" style="display:none"><div class="loader"></div></div>
      </div>
    </form>
    </div>
  </div>
</div>

<!--end delete modal-->
