import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { first } from 'rxjs/operators';

import { MemberService, AlertService, AuthenticationService } from '@app/_services';
import { Role, User, UserFormMode } from '@app/_models';

@Component({ templateUrl: 'add-edit.component.html' })
export class AddEditComponent implements OnInit {
    form!: FormGroup;
    id!: number;
    formMode!: UserFormMode;
    loading = false;
    submitted = false;
    apiUser! : User;    

    constructor(
        private formBuilder: FormBuilder,
        private route: ActivatedRoute,
        private router: Router,
        private memberService: MemberService,
        private alertService: AlertService,
        private authenticationService: AuthenticationService
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
            fullname: ['', Validators.required],
            suspended: [{value: '', disabled: true}],
            username: ['', [Validators.required]],
            role: ['', Validators.required],
            password: ['', [Validators.minLength(8), (this.formMode == UserFormMode.Add) ? Validators.required : Validators.nullValidator]],
            confirmPassword: ['', (this.formMode == UserFormMode.Add) ? Validators.required : Validators.nullValidator]
        });

        if (this.formMode != UserFormMode.Add) {
            this.memberService.getById(this.id)
                .pipe(first())
                .subscribe(x => this.form.patchValue(x));
        }
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
}