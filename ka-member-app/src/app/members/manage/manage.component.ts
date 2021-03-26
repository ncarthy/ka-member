import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Location } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  FormArray,
  Validators,
  ReactiveFormsModule,
} from '@angular/forms';

import { Member, MembershipStatus, Transaction, User } from '@app/_models';
import {
  AlertService,
  AuthenticationService,
  MemberNameService,
  MemberService,
  MembershipStatusService,
  TransactionService,
} from '@app/_services';
import { Observable } from 'rxjs';
import { switchAll, switchMap } from 'rxjs/operators';

@Component({
  selector: 'app-manage',
  templateUrl: './manage.component.html',
  styleUrls: ['./manage.component.css'],
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
    private membershipStatusService: MembershipStatusService
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit(): void {
    this.loading = true;

    this.form = this.formBuilder.group({
      postonhold: [false],
      statusID: [null, Validators.required],
      expirydate: [null],
      joindate: [null],
      reminderdate: [null],
      deletedate: [null],
      multiplier: [null],
      membershipfee: [null],
      name: [{value: null, disabled: true}]
    });

    this.memberService
      .getById(this.route.snapshot.params['idmember'])
      .pipe(
        switchMap((m: Member) => {
          this.member = m;
          this.form.patchValue(m);
          return this.transactionService.getByMember(m.id);
        }),
        switchMap((txs: Transaction[]) => {
          this.transactions = txs;
          return this.memberNameService.getNamesStringForMember(this.member!.id);
        }),
        switchMap((mn: string) => {
          this.f['name'].setValue(mn);                
          return this.membershipStatusService.getAll();
        })
      )
      .subscribe((statuses: MembershipStatus[]) => {
        this.statuses = statuses;
        this.loading = false;
      });

  }

  goBack() {
    this.location.back();
    return false; // don't propagate event
  }

  /** Reload transacitons table and add/edit transaction card */
  onReloadRequested(e: any) {
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

  onSubmit() {

  }

    // convenience getters for easy access to form fields
    get f() {
      return this.form.controls;
    }
}
