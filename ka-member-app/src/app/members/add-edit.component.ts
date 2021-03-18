import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import {
  AbstractControl,
  FormBuilder,
  FormGroup,
  FormArray,
  Validators,
  ReactiveFormsModule,
} from '@angular/forms';

import { first } from 'rxjs/operators';

import {
  MemberService,
  AlertService,
  AuthenticationService,
  CountryService,
  MembershipStatusService,
} from '@app/_services';
import {
  Address,
  Country,
  MembershipStatus,
  Role,
  User,
  UserFormMode,
} from '@app/_models';
import { phoneNumberRegex } from '@app/shared/regexes.const';
import { AddressFormValue } from '@app/shared/address-form/address-form-value.interface';

@Component({
  templateUrl: 'add-edit.component.html',
  styleUrls: ['./add-edit.component.css'],
})
export class AddEditComponent implements OnInit {
  form!: FormGroup;
  id!: number;
  formMode!: UserFormMode;
  loading = false;
  submitted = false;
  apiUser!: User;
  countries!: Country[];
  statuses!: MembershipStatus[];
  primaryAddress!: AddressFormValue;
  secondaryAddress!: AddressFormValue;

  constructor(
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private memberService: MemberService,
    private alertService: AlertService,
    private authenticationService: AuthenticationService,
    private countryService: CountryService,
    private membershipStatusService: MembershipStatusService
  ) {
    this.apiUser = authenticationService.userValue;
  }

  ngOnInit() {
    this.loading = true;

    this.id = this.route.snapshot.params['id'];

    if (!this.id) {
      this.formMode = UserFormMode.Add;
    } else {
      this.formMode = UserFormMode.Edit;
    }

    this.form = this.formBuilder.group({
      // Checkboxes
      gdpr_email: [false],
      gdpr_tel: [false],
      gdpr_address: [false],
      gdpr_sm: [false],
      postonhold: [false],
      showSecondaryAdress: [false],

      // Individual / Corporate/ Lifetime etc.
      statusID: [null, Validators.required],

      primaryAddress: [null, [Validators.required]],
      secondaryAddress: [null],

      email1: [null, [Validators.email]],
      phone1: [null, [Validators.pattern(phoneNumberRegex)]],
      email2: [null, [Validators.email]],
      phone2: [null, [Validators.pattern(phoneNumberRegex)]],

      expirydate: [null],
      joindate: [null],
      reminderdate: [null],
      deletedate: [null],

      username: [{ value: '', disabled: true }],
      updatedate: [{ value: null, disabled: true }],

      businessname: [''],
      title: [''],

      bankpayerref: [''],
      note: [''],

      multiplier: [''],
      membershipfee: [''],

      area: [''],
      repeatpayment: [0],
      recurringpayment: [0],

    });

    this.countryService
      .getAll()
      .pipe(first())
      .subscribe((x) => {
        this.countries = x;
      });
    this.membershipStatusService
      .getAll()
      .pipe(first())
      .subscribe((x) => {
        this.statuses = x;
        if (this.formMode === UserFormMode.Add) {
          this.loading = false;
        }
      });

    this.form.valueChanges.subscribe((x: any) => {
      console.log(x);

      if (x.primaryAddress) {
        x.addressfirstline = x.primaryAddress.addressLine1 || null;
        x.addresssecondline = x.primaryAddress.addressLine2 || null;
        x.city = x.primaryAddress.city || null;
        x.county = x.primaryAddress.county || null;
        x.postcode = x.primaryAddress.postcode || null;
        x.countryID = x.primaryAddress.country  || null;
      }

      if (x.secondaryAddress && x.secondaryAddress.addressLine1 ) {
        x.addressfirstline2 = x.secondaryAddress.addressLine1 || null;
        x.addresssecondline2 = x.secondaryAddress.addressLine2 || null;
        x.city2 = x.secondaryAddress.city || null;
        x.county2 = x.secondaryAddress.county || null;
        x.postcode2 = x.secondaryAddress.postcode || null;
        x.country2ID = x.secondaryAddress.country  || null;
      } else {
        x.addressfirstline2 = '';
        x.addresssecondline2 = '';
        x.city2 = '';
        x.county2 = '';
        x.postcode2 = '';
        x.country2ID = null;
      }
    });

    if (this.formMode != UserFormMode.Add) {
      this.memberService
        .getById(this.id)
        .pipe(first())
        .subscribe((x) => {
          this.form.patchValue(x);

          this.primaryAddress = {
            addressLine1: x.addressfirstline,
            addressLine2: x.addresssecondline,
            city: x.city,
            county: x.county,
            postcode: x.postcode,
            country: { id: x.countryID, name: '' },
          };

          this.secondaryAddress = {
            addressLine1: x.addressfirstline2,
            addressLine2: x.addresssecondline2,
            city: x.city2,
            county: x.county2,
            postcode: x.postcode2,
            country: { id: x.country2ID, name: '' },
          };

          if (x.addressfirstline2) {
            this.form.controls['showSecondaryAdress'].setValue(true);
          }

          this.loading = false;
        });
    }
  }

  // convenience getter for easy access to form fields
  get f() {
    return this.form.controls;
  }

  onSubmit() {
    this.submitted = true;

    // reset alerts on submit
    this.alertService.clear();

    // stop here if form is invalid
    if (this.form.invalid) {
      const list = this.findInvalidControlsRecursive(this.form);
      return;
    }

    this.loading = true;
    if (this.formMode == UserFormMode.Add) {
      this.createMember();
    } else {
      this.updateMember();
    }
  }

  get isMemberAdd() {
    return this.formMode == UserFormMode.Add;
  }
  get isMemberEdit() {
    return this.formMode == UserFormMode.Edit;
  }

  private createMember() {
    this.memberService
      .create(this.form.value)
      .pipe(first())
      .subscribe(
        result => {
            // Handle result
            console.log(result)
          },
          error => {
            console.log(error);
            this.alertService.error('Unable to add new member.', {
                keepAfterRouteChange: true,
              });
          },  
        () => {
        this.alertService.success('Member added', {
          keepAfterRouteChange: true,
        });
        this.router.navigate(['../'], { relativeTo: this.route });
      })
      .add(() => (this.loading = false));
  }

  private updateMember() {
    this.memberService
      .update(this.id, this.form.value)
      .pipe(first())
      .subscribe(() => {
        this.alertService.success('Member updated', {
          keepAfterRouteChange: true,
        });

        if (this.formMode == UserFormMode.Edit) {
          this.router.navigate(['../../'], { relativeTo: this.route });
        } else {
          this.router.navigate(['/'], { relativeTo: this.route });
        }
      },
      () => {
        this.alertService.error('Member not updated', {
            keepAfterRouteChange: true,
          });
      })
      .add(() => (this.loading = false));
  }

  /* 
   Returns an array of invalid control/group names, or a zero-length array if 
   no invalid controls/groups where found. Uses recursive JS function.
*/
  private findInvalidControlsRecursive(
    formToInvestigate: FormGroup | FormArray
  ): string[] {
    var invalidControls: string[] = [];
    let recursiveFunc = (form: FormGroup | FormArray) => {
      Object.keys(form.controls).forEach((field) => {
        const control = form.get(field);
        if (control && control.invalid) invalidControls.push(field);
        if (control instanceof FormGroup) {
          recursiveFunc(control);
        } else if (control instanceof FormArray) {
          recursiveFunc(control);
        }
      });
    };
    recursiveFunc(formToInvestigate);
    return invalidControls;
  }
}
