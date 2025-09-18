import { Component, inject, OnInit } from '@angular/core';
import { Location, NgClass } from '@angular/common';
import { Router, ActivatedRoute, RouterLink } from '@angular/router';
import {
  FormBuilder,
  FormGroup,
  FormArray,
  Validators,
  ReactiveFormsModule,
} from '@angular/forms';

import { NgbModal, NgbDatepickerModule, NgbTooltipModule } from '@ng-bootstrap/ng-bootstrap';

import {} from 'googlemaps';

import { from, throwError } from 'rxjs';
import { map, concatMap, catchError } from 'rxjs/operators';

import {
  AlertService,
  AuthenticationService,
  CountryService,
  MemberService,
  MemberNameService,
  MembershipStatusService,
} from '@app/_services';

import {
  Address,
  Country,
  FormMode,
  MemberName,
  MembershipStatus,
  User,
} from '@app/_models';
import { phoneNumberRegex } from '@app/shared/regexes.const';
import {
  MemberAnonymizeConfirmModalComponent,
  MemberDeleteConfirmModalComponent,
} from '../modals';
import { SharedModule } from '@app/shared/shared.module'

@Component({
  standalone: true,
    templateUrl: 'add-edit.component.html',
    styleUrls: ['add-edit.component.css'],
    imports: [SharedModule, NgbDatepickerModule, NgbTooltipModule, NgClass, ReactiveFormsModule, RouterLink]
})
export class MemberAddEditComponent implements OnInit {
  form!: FormGroup;
  id!: number;
  formMode!: FormMode;
  loading = false;
  submitted = false;
  user!: User;
  countries!: Country[];
  statuses!: MembershipStatus[];
  primaryAddress!: Address;
  secondaryAddress!: Address;

  private formBuilder = inject(FormBuilder);
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private location = inject(Location);
  private alertService = inject(AlertService);
  private authenticationService = inject(AuthenticationService);
  private countryService = inject(CountryService);
  private memberService = inject(MemberService);
  private memberNameService = inject(MemberNameService);
  private membershipStatusService = inject(MembershipStatusService);
  public modalService = inject(NgbModal);

  constructor(

  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.loading = true;

    this.id = this.route.snapshot.params['id'];

    if (!this.id) {
      this.formMode = FormMode.Add;
    } else {
      this.formMode = FormMode.Edit;
    }

    this.form = this.formBuilder.group({
      names: new FormArray([]), //https://jasonwatmore.com/post/2020/09/18/angular-10-dynamic-reactive-forms-example
      // Checkboxes
      gdpr_email: [false],
      gdpr_tel: [false],
      gdpr_address: [false],
      gdpr_sm: [false],
      postonhold: [false],
      emailonhold: [false],
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
      deletedate: [{ value: null, disabled: true }],

      username: [{ value: '', disabled: true }],
      updatedate: [{ value: null, disabled: true }],

      businessname: [''],
      title: [''],

      bankpayerref: [''],
      note: [''],

      // These fields are updated by the 'manage' component
      // They are retained here so that the field values are not
      // lost when the Member is updated.
      multiplier: [''],
      membershipfee: [''],
      area: [''],
      repeatpayment: [0],
      recurringpayment: [0],
    });

    // Fill country dropdown
    this.countryService.getAll().subscribe((x) => {
      this.countries = x;
    });

    // Fill status dropdown
    this.membershipStatusService.getAll().subscribe((x) => {
      this.statuses = x;
      if (this.formMode === FormMode.Add) {
        this.loading = false;
      }
    });

    // Member names
    if (this.formMode === FormMode.Add) {
      this.onAddName(); // Add one blank name
    } else {
      this.memberNameService
        .getAllForMember(this.id)
        .pipe(
          map((names: MemberName[]) => {
            for (let name of names) {
              this.onAddName(name.honorific, name.firstname, name.surname);
            }
          }),
        )
        .subscribe();
    }

    if (this.formMode === FormMode.Add) {
      // Initialize the 'Join Date' field with today's date for New Members
      // From https://stackoverflow.com/a/35922073/6941165
      this.form.controls['joindate'].setValue(
        new Date().toISOString().slice(0, 10),
      );
    }

    // Populate the form
    if (this.formMode === FormMode.Edit) {
      this.memberService
        .getById(this.id)
        .subscribe((x) => {
          this.form.patchValue(x);

          this.primaryAddress = x.primaryAddress;
          this.secondaryAddress = x.secondaryAddress;

          if (this.secondaryAddress && this.secondaryAddress.addressfirstline) {
            this.form.controls['showSecondaryAdress'].setValue(true);
          }

          if (x.expirydate && x.expirydate.toString() == '0000-00-00') {
            this.form.controls['expirydate'].setValue(null);
          }
        })
        .add(() => (this.loading = false));
    }
  }

  // convenience getters for easy access to form fields
  get f() {
    return this.form.controls;
  }
  get n() {
    return this.f.names as FormArray;
  }
  get namesFormGroups() {
    return this.n.controls as FormGroup[];
  }

  onAddName(honorific = '', firstname = '', surname = '') {
    this.n.push(
      this.formBuilder.group({
        honorific: [honorific],
        firstname: [firstname],
        surname: [surname, [Validators.required]],
      }),
    );
  }

  onRemoveName(index: number) {
    if (this.n.length > 1 && index) {
      this.n.removeAt(index);
    }
  }

  onSubmit() {
    this.submitted = true;

    // reset alerts on submit
    this.alertService.clear();

    // stop here if form is invalid
    if (this.form.invalid) {
      const controls = this.form.controls;
      for (const name in controls) {
        if (controls[name].invalid) {
          console.log('Invalid control: ' + name);
        }
      }

      return;
    }

    this.loading = true;
    if (this.formMode === FormMode.Add) {
      this.createMember();
    } else {
      this.updateMember();
    }
  }

  onReset() {
    this.submitted = false;

    // reset alerts on submit
    this.alertService.clear();

    this.form.reset({
      primaryAddress: {},
      secondaryAddress: {},
      showSecondaryAdress: false,
    });
  }

  goBack() {
    this.location.back();
  }

  get isMemberAdd() {
    return this.formMode == FormMode.Add;
  }
  get isMemberEdit() {
    return this.formMode == FormMode.Edit;
  }

  private createMember() {
    this.memberService
      .create(this.form.value)
      .pipe(
        concatMap((success: any) => {
          return this.memberNameService.updateAllForMember(
            // Use of non-null assertion operator
            // https://www.typescriptlang.org/docs/handbook/release-notes/typescript-2-0.html#non-null-assertion-operator
            success.id!,
            this.form.value.names,
          );
        }),
        catchError((err) => throwError(err)),
      )
      .subscribe({
        next: () => {
          this.alertService.success('Member added', {
            keepAfterRouteChange: true,
          });
          this.goBack();
        },
        error: (error) => {
          console.log(error);
          this.alertService.error('Unable to add new member.', {
            keepAfterRouteChange: true,
          });
        },
      })
      .add(() => (this.loading = false));
  }

  private updateMember() {
    this.memberService
      .update(this.id, this.form.value)
      .pipe(
        concatMap((success: any) => {
          // subscribe to this Observable after the other completes
          return this.memberNameService.updateAllForMember(
            // Use of non-null assertion operator
            // https://www.typescriptlang.org/docs/handbook/release-notes/typescript-2-0.html#non-null-assertion-operator
            success.id!,
            this.form.value.names,
          );
        }),
        catchError((err) => throwError(err)),
      )
      .subscribe({
        next: (v) => {
          this.alertService.success('Member updated', {
            keepAfterRouteChange: true,
          });

          this.goBack();
        },
        error: (e) => {
          this.alertService.error('Member not updated', {
            keepAfterRouteChange: true,
          });
        },
      })
      .add(() => (this.loading = false));
  }

  onAnonymize() {
    from(
      this.modalService.open(MemberAnonymizeConfirmModalComponent).result,
    ).subscribe((success) => {
      this.memberService.anonymize(this.id).subscribe({
        next: () => {
          this.alertService.success('Member anonymized', {
            keepAfterRouteChange: true,
          });
          this.goBack();
        },
        error: () =>
          this.alertService.error('Unable to anonymize member.', {
            keepAfterRouteChange: true,
          }),
      });
    }); // If user dismisses the modal just ignore it
  }

  onSetToFormer() {
    this.memberService.setToFormer(this.id).subscribe({
      next: () => {
        this.alertService.success('Set to "former member" succeeded.', {
          keepAfterRouteChange: true,
        });
        this.router.navigate(['/members'], { relativeTo: this.route });
      },
      error: () =>
        this.alertService.error('Unable to set member to "former member".', {
          keepAfterRouteChange: true,
        }),
    });
  }

  onDelete() {
    from(
      this.modalService.open(MemberDeleteConfirmModalComponent).result,
    ).subscribe((success) => {
      this.memberService.delete(this.id).subscribe({
        next: () => {
          this.alertService.success('Member deleted', {
            keepAfterRouteChange: true,
          });
          this.router.navigate(['/members'], { relativeTo: this.route });
        },
        error: () =>
          this.alertService.error('Unable to delete member.', {
            keepAfterRouteChange: true,
          }),
      });
    }); // If user dismisses the modal just ignore it
  }

  showAnonymizeButton() {
    return this.user.isAdmin &&
    this.f['statusID'] &&
    this.f['statusID'].value === 9 &&
    this.namesFormGroups &&
    this.namesFormGroups[0] &&
    this.namesFormGroups[0].value.surname !== 'Anonymized'
  }
}
