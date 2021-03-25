import {
  Component,
  forwardRef,
  Input,
  OnChanges,
  OnDestroy,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import {
  ControlValueAccessor,
  FormBuilder,
  NG_VALUE_ACCESSOR, // Example: https://github.com/xiongemi/angular-form-ngxs/
  Validators,
} from '@angular/forms';

import { switchMap } from 'rxjs/operators';

import {
  FormMode,
  Transaction,
  PaymentType,
  BankAccount,
  User,
} from '@app/_models';

import {
  AuthenticationService,
  BankAccountService,
  PaymentTypeService,
  TransactionService,
} from '@app/_services';
import { Subscription } from 'rxjs';

@Component({
  selector: 'transaction-add-edit',
  templateUrl: './add-edit.component.html',
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => TransactionAddEditComponent),
      multi: true,
    },
  ],
})
export class TransactionAddEditComponent implements ControlValueAccessor, OnInit, OnDestroy, OnChanges {
  @Input() touched: boolean = false;
  @Input() transaction?: Transaction;

  id!: number;
  formMode!: FormMode;
  loading = false;
  onChange: any = (_: Transaction) => {};
  onTouch: any = () => {};
  submitted: boolean = false;
  user!: User;
  bankAccounts?: BankAccount[];
  paymentTypes?: PaymentType[];

  private subscription = new Subscription();

  transactionForm = this.fb.group({
    date: [null, Validators.required],
    amount: [null, Validators.required],
    paymenttypeID: [null],
    bankID: [null],
    note: [null],
  });

  constructor(
    private fb: FormBuilder,
    private authenticationService: AuthenticationService,
    private transactionService: TransactionService,
    private bankAccountService: BankAccountService,
    private paymentTypeService: PaymentTypeService
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit(): void {
    this.loading = true;

    this.bankAccountService
    .getAll()
    .pipe(
      switchMap((banks: BankAccount[]) => {
        this.bankAccounts = banks;
        return this.paymentTypeService.getAll();
      })
    )
    .subscribe((types: PaymentType[]) => {
      this.paymentTypes = types;
      this.loading = false;
    });

    this.subscription.add(
      this.transactionForm.valueChanges.subscribe((value: Transaction) => {
        this.onChange(value);
      })
    );
  }

  ngOnDestroy() {
    this.subscription.unsubscribe();
  }

  ngOnChanges(simpleChanges: SimpleChanges) {
    if (simpleChanges['touched'] && simpleChanges['touched'].currentValue) {
      this.transactionForm.markAllAsTouched();
    }
  }

  writeValue(value: null | Transaction): void {
    if (value) {
      this.transactionForm.reset(value);
    }
  }

  registerOnChange(fn: () => {}): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: (_: Transaction) => {}): void {
    this.onTouch = fn;
  }

  // convenience getters for easy access to form fields
  get f() {
    return this.transactionForm.controls;
  }

  // Required so that the template can access the Enum
  // From https://stackoverflow.com/a/59289208
  public get FormMode() {
    return FormMode;
  }
}
