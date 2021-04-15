import { Component, Input, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

import { Member, MemberSearchResult, User } from '@app/_models';
import {
  AuthenticationService,
  MemberService,
  UserService,
} from '@app/_services';

@Component({
  selector: 'email-client',
  templateUrl: './email-client.component.html',
  styleUrls: ['./email-client.component.css'],
})
export class EmailClientComponent implements OnInit {
  @Input() member?: MemberSearchResult;
  memberFull?: Member;
  user?: User;
  form!: FormGroup;
  submitted = false;

  constructor(
    public modal: NgbActiveModal,
    private authenticationService: AuthenticationService,
    private userService: UserService,
    private formBuilder: FormBuilder,
    private memberService: MemberService
  ) {
    this.userService
      .getById(this.authenticationService.userValue.id)
      .subscribe((u: User) => {
        this.user = u;

        this.form = this.formBuilder.group({
          to: ['', [Validators.required, Validators.email]],
          from: [this.user.email, [Validators.required, Validators.email]],
          salutation: [''],
          body: [{ value: '', disabled: true }],
          fromName: [this.user.fullname],
          fromTitle: [this.user.title],
        });
      });
  }

  ngOnInit(): void {
    this.memberService.getById(this.member!.id).subscribe((m: Member) => {
      let email = m.email1?m.email1:m.email2;
      this.f['to'].setValue(email);
      this.memberFull = m;
      this.f['salutation'].setValue('Dear '+this.member?.name+',');
    });
  }

  // convenience getters for easy access to form fields
  get f() {
    return this.form.controls;
  }
}
