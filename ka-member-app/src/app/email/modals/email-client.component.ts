import { Component, Input, OnInit } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

import { switchMap, debounceTime } from 'rxjs/operators';

import { Member, MemberSearchResult, User } from '@app/_models';
import {
  AuthenticationService,
  EmailService,
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
  submitted = true;
  loading = false;
  body?: any;

  constructor(
    public modal: NgbActiveModal,
    private authenticationService: AuthenticationService,
    private userService: UserService,
    private formBuilder: FormBuilder,
    private memberService: MemberService,
    private emailService: EmailService,
    private sanitizer: DomSanitizer
  ) {
    this.loading = true;
    this.form = this.formBuilder.group({
      idmember: [null, Validators.required],
      toEmail: ['', [Validators.required, Validators.email]],
      fromEmail: ['', [Validators.required, Validators.email]],
      salutation: [''],
      fromName: [''],
      fromTitle: [''],
      subject: ['Membership Renewal', Validators.required]
    });

    this.form.valueChanges
      .pipe(
        debounceTime(500),
        switchMap(() => {
          return this.emailService.prepareReminderEmail(this.form.value);
        })
      )
      .subscribe((response: any) => {
        if (response.html) {
          this.setBody(response.html);
        }
      });
  }

  ngOnInit(): void {
    this.memberService
      .getById(this.member!.id)
      .pipe(
        switchMap((m: Member) => {
          this.memberFull = m;
          this.f['idmember'].setValue(m.id);
          let email = m.email1 ? m.email1 : m.email2;
          this.f['toEmail'].setValue(email);
          this.f['salutation'].setValue('Dear ' + this.member!.name + ',');
          return this.userService.getById(
            this.authenticationService.userValue.id
          );
        })
      )
      .subscribe((u: User) => {
        this.user = u;
        this.f['fromEmail'].setValue(u.email);
        this.f['fromName'].setValue(u.fullname);
        this.f['fromTitle'].setValue(u.title);
      })
      .add(() => (this.loading = false));
  }

  // convenience getters for easy access to form fields
  get f() {
    return this.form.controls;
  }

  private setBody(html: string) {
    this.body = this.sanitizer.bypassSecurityTrustHtml(html); // this line bypasses angular security
  }

  onSend() {
    this.emailService.sendReminderEmail(this.form.value).subscribe(
      (response: any) => {
        this.modal.close('OK');
      },
      (error: any) => this.modal.dismiss('Fail')
    );
  }

  // Hash function from https://stackoverflow.com/a/52171480
  /*private cyrb53(str: string, seed = 0): number {
    let h1 = 0xdeadbeef ^ seed,
      h2 = 0x41c6ce57 ^ seed;
    for (let i = 0, ch; i < str.length; i++) {
      ch = str.charCodeAt(i);
      h1 = Math.imul(h1 ^ ch, 2654435761);
      h2 = Math.imul(h2 ^ ch, 1597334677);
    }
    h1 =
      Math.imul(h1 ^ (h1 >>> 16), 2246822507) ^
      Math.imul(h2 ^ (h2 >>> 13), 3266489909);
    h2 =
      Math.imul(h2 ^ (h2 >>> 16), 2246822507) ^
      Math.imul(h1 ^ (h1 >>> 13), 3266489909);
    return 4294967296 * (2097151 & h2) + (h1 >>> 0);
  }*/
}
