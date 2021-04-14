import { Component, Input, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

import { MemberSearchResult, User } from '@app/_models';
import { AuthenticationService, UserService } from '@app/_services';

@Component({
  selector: 'email-client',
  templateUrl: './email-client.component.html',
  styleUrls: ['./email-client.component.css'],
})
export class EmailClientComponent implements OnInit {
  @Input() member?: MemberSearchResult;
  user?: User;
  form!: FormGroup;
  submitted = false;

  constructor(
    public modal: NgbActiveModal,
    private authenticationService: AuthenticationService,
    private userService: UserService,
    private formBuilder: FormBuilder
  ) {
    this.userService
      .getById(this.authenticationService.userValue.id)
      .subscribe((u: User) => {
        this.user = u;

        this.form = this.formBuilder.group({
          to: ['', [Validators.required, Validators.email]],
          from: [this.user.email, [Validators.required, Validators.email]],
          emailBody: [''],
        });
      });
  }

  ngOnInit(): void {
    
  }

    // convenience getters for easy access to form fields
    get f() {
      return this.form.controls;
    }
}
