import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Location } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  Validators,
  ReactiveFormsModule,
} from '@angular/forms';

import {
  Address,
  AddresstoHTML,
  Member,
  MembershipStatus,
  Transaction,
  User,
} from '@app/_models';
import {
  AlertService,
  AuthenticationService,
  MemberNameService,
  MemberService,
  MembershipStatusService,
  TransactionService,
} from '@app/_services';
import { switchMap } from 'rxjs/operators';

@Component({
    templateUrl: './manage.component.html',
    styleUrls: ['./manage.component.css'],
    standalone: true
})
export class MemberManageComponent implements OnInit {
  form!: FormGroup;
  loading: boolean = false;
  submitted: boolean = false;
  member?: Member;
  memberName?: string;
  statuses!: MembershipStatus[];
  transactions?: Transaction[];
  transactionToEdit?: Transaction;
  user: User;

  constructor(
    private location: Location,
    private route: ActivatedRoute,
    private formBuilder: FormBuilder,
    private alertService: AlertService,
    private memberService: MemberService,
    private transactionService: TransactionService,
    private memberNameService: MemberNameService,
    private authenticationService: AuthenticationService,
    private membershipStatusService: MembershipStatusService,
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit(): void {
    this.loading = true;

    this.form = this.formBuilder.group({
      postonhold: [false],
      emailonhold: [false],
      statusID: [null, Validators.required],
      expirydate: [null],
      joindate: [null],
      reminderdate: [null],
      deletedate: [null],
      multiplier: [null],
      defaultMultiplier: [{ value: null, disabled: true }],
      membershipfee: [null],
      defaultMembershipfee: [{ value: null, disabled: true }],
      name: [{ value: null, disabled: true }],
      address: [{ value: null, disabled: true }],
      note: [null],
      username: [null],
      updatedate: [null],
      gpslat1: [null],
      gpslat2: [null],
      gpslng1: [null],
      gpslng2: [null],
    });

    this.memberService
      .getById(this.route.snapshot.params['idmember'])
      .pipe(
        switchMap((m: Member) => {
          this.member = m;
          this.form.patchValue(m);
          if (m && m.primaryAddress) {
            this.f['address'].setValue(AddresstoHTML(m.primaryAddress));
          }
          return this.transactionService.getByMember(m.id);
        }),
        switchMap((txs: Transaction[]) => {
          this.transactions = txs;
          return this.memberNameService.getNamesStringForMember(
            this.member!.id,
          );
        }),
        switchMap((mn: string) => {
          this.f['name'].setValue(mn);
          return this.membershipStatusService.getAll();
        }),
      )
      .subscribe((statuses: MembershipStatus[]) => {
        this.statuses = statuses;
        this.setMembershipDefaults(this.member?.statusID);
        this.loading = false;
      });
  }

  goBack() {
    this.location.back();
    return false; // don't propagate event
  }

  /** Reload transacitons table and add/edit transaction card */
  onReloadRequested(t: Transaction) {
    if (this.member) {
      this.loading = true;
      this.transactionService
        .getByMember(this.member.id)
        .subscribe((txs: Transaction[]) => {
          this.transactions = txs;
          this.loading = false;
        });
    }
  }

  onEditRequested(tx: Transaction) {
    this.transactionToEdit = tx;
  }

  /** Update the member in the database */
  onSubmit() {
    if (this.member) {
      this.loading = true;

      this.member.postonhold = this.form.value.postonhold;
      this.member.emailonhold = this.form.value.emailonhold;
      this.member.statusID = this.form.value.statusID;
      this.member.multiplier = this.form.value.multiplier;
      this.member.membershipfee = this.form.value.membershipfee;
      this.member.joindate = this.form.value.joindate;
      this.member.expirydate = this.form.value.expirydate;
      this.member.reminderdate = this.form.value.reminderdate;
      this.member.deletedate = this.form.value.deletedate;
      this.member.note = this.form.value.note;

      this.memberService
        .update(this.member.id, this.member)
        .subscribe(() => {
          this.alertService.success('Member updated', {
            keepAfterRouteChange: true,
          });

          this.goBack();
        })
        .add(() => (this.loading = false));
    }
  }

  // convenience getters for easy access to form fields
  get f() {
    return this.form.controls;
  }

  /** When the user changes the membership status then update the
   *  multiplier and membership fee
   */
  onStatusChange(e: string) {
    const idx = parseInt(e.substring(e.length - 2));
    this.setMembershipDefaults(idx);
    // If change to former member then set delete date
    if (idx === 9) {
      // Initialize the 'Join Date' field with today's date for New Members
      // From https://stackoverflow.com/a/35922073/6941165
      this.form.controls['deletedate'].setValue(
        new Date().toISOString().slice(0, 10),
      );
    }
  }

  setMembershipDefaults(membershipStatusID: number | undefined) {
    if (!membershipStatusID) {
      return;
    }
    const status = this.statuses.find((o) => o.id === membershipStatusID);
    if (status) {
      this.f['defaultMultiplier'].setValue(status.multiplier);
      this.f['defaultMembershipfee'].setValue(status.membershipfee);
    }
  }

  onReset() {
    this.form.patchValue(this.member!);
    this.setMembershipDefaults(this.member?.statusID);
  }
}
