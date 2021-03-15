import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { AbstractControl, FormBuilder, FormGroup, Validators, ReactiveFormsModule  } from '@angular/forms';

import { first } from 'rxjs/operators';

import { MemberService, AlertService, AuthenticationService, CountryService } from '@app/_services';
import { Country, Role, User, UserFormMode } from '@app/_models';

@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent implements OnInit {
    form!: FormGroup;
    id!: number;
    formMode!: UserFormMode;
    loading = false;
    submitted = false;
    apiUser! : User;    
    countries!: Country[];
    countryControl!: AbstractControl;

    constructor(
        private formBuilder: FormBuilder,
        private route: ActivatedRoute,
        private router: Router,
        private memberService: MemberService,
        private alertService: AlertService,
        private authenticationService: AuthenticationService,
        private countryService: CountryService
    ) {
        this.apiUser = authenticationService.userValue;
    }

    ngOnInit() {
        this.id = this.route.snapshot.params['id'];       

        if (!this.id) {
            this.formMode = UserFormMode.Add;
        } else {
            this.formMode = UserFormMode.Edit;
        }

        this.form = this.formBuilder.group({
            //name: ['', Validators.required],

            // Checkboxes
            gdpr_email: [''],
            gdpr_tel: [''],
            gdpr_address: [''],
            gdpr_sm: [''],
            postonhold: [''],

            // Individual / Corporate/ Lifetime etc.
            statusID: [null, Validators.required],

            addressfirstline: ['', [Validators.required]],
            addresssecondline: [''],
            city: ['', [Validators.required]],
            county: [''],
            postcode: ['', [Validators.required]],
            countryID: [null, Validators.required],
            email1: ['', [Validators.email]],
            phone1: ['', [Validators.pattern('[- +()0-9]+')]], // From https://stackoverflow.com/a/65589987/6941165

            expirydate: [''],
            joindate: [''],
            reminderdate: [''],
            deletedate: [''],

            username: [{value: '', disabled: true}],
            updatedate: [''],

            businessname: [''],
            jobtitle: [''],

            bankpayerref: [''],
            note: [''],

            addressfirstline2: ['', [Validators.nullValidator]],
            addresssecondline2: [''],
            city2: ['', [Validators.nullValidator]],
            county2: [''],
            postcode2: ['', [Validators.nullValidator]],
            countryID2: ['', Validators.nullValidator],
            email2: ['', [Validators.email]],
            phone2: ['', [Validators.pattern('[- +()0-9]+')]],
            
        });

        this.countryService.getAll().pipe(first())
        .subscribe(x => {
            this.countries = x;
        }); 

        if (this.formMode != UserFormMode.Add) {
            this.memberService.getById(this.id)
                .pipe(first())
                .subscribe(x => {
                    this.form.patchValue(x);
                    //this.form.controls['countryID'].setValue(x.countryID);
                });
        }

        this.countryControl = this.form.controls['countryID'];
        this.countryControl.valueChanges.subscribe((cty:any) => {
            console.log('Country changed to:', cty);
            });
    }

    // convenience getter for easy access to form fields
    get f() { return this.form.controls; }

    // public protperty to simplify controls If status
    get isAdmin() {
        return this.apiUser && this.apiUser.role &&  this.apiUser.role === Role.Admin;
    }

    onSubmit() {
        this.submitted = true;

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.loading = true;
        if (this.formMode == UserFormMode.Add) {
            this.createMember();
        } else {
            this.updateMember();
        }
    }

    get isMemberAdd() { return this.formMode == UserFormMode.Add; }
    get isMemberEdit() { return this.formMode == UserFormMode.Edit; }

    private createMember() {
        this.memberService.create(this.form.value)
            .pipe(first())
            .subscribe(() => {
                this.alertService.success('Member added', { keepAfterRouteChange: true });
                this.router.navigate(['../'], { relativeTo: this.route });
            })
            .add(() => this.loading = false);
    }

    private updateMember() {
        this.memberService.update(this.id, this.form.value)
            .pipe(first())
            .subscribe(() => {
                this.alertService.success('Member updated', { keepAfterRouteChange: true });

                if (this.formMode == UserFormMode.Edit) {
                    this.router.navigate(['../../'], { relativeTo: this.route });    
                } else {
                    this.router.navigate(['/'], { relativeTo: this.route });
                }
                
            })
            .add(() => this.loading = false);
    }

    compareCountries(val1: Country, val2: Country) {
        return val1 && val2 && val1 === val2;
      }
}