import {
  Component,
  EventEmitter,
  forwardRef,
  Input,
  OnChanges,
  OnDestroy,
  OnInit,
  Output,
  SimpleChanges,
} from '@angular/core';
import {
  ControlValueAccessor,
  FormGroup,
  UntypedFormBuilder,
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
  Member,
} from '@app/_models';

import {
  AlertService,
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
export class TransactionAddEditComponent
  implements ControlValueAccessor, OnInit, OnDestroy, OnChanges
{
  @Input() touched: boolean = false;
  @Input() transaction?: Transaction;
  @Input() mostRecentTransaction?: Transaction;
  @Input() member?: Member;
  @Output() reloadRequested: EventEmitter<Transaction>;

  formMode!: FormMode;

  loading: boolean = false;
  saving: boolean = false;
  submitted: boolean = false;

  // the underscore mean the function will be passed one argument, but that you don't care about it.
  onChange: any = (_: Transaction) => {};
  onTouch: any = () => {};
  user!: User;
  banks?: BankAccount[];
  paymentTypes?: PaymentType[];

  private subscription = new Subscription();

  transactionForm!: FormGroup<any>;

  constructor(
    private fb: UntypedFormBuilder,
    private authenticationService: AuthenticationService,
    private transactionService: TransactionService,
    private bankAccountService: BankAccountService,
    private paymentTypeService: PaymentTypeService,
    private alertService: AlertService
  ) {
    this.user = this.authenticationService.userValue;
    this.reloadRequested = new EventEmitter();
  }

  ngOnInit(): void {
    this.loading = true;
    this.formMode = FormMode.Add;

    this.transactionForm = this.fb.group({
      date: [null, Validators.required],
      amount: [null, [Validators.required]],
      paymenttypeID: [null, [Validators.required]],
      bankID: [null, [Validators.required]],
      note: [null],
      idmember: [null, Validators.required],
    });

    this.bankAccountService
      .getAll()
      .pipe(
        switchMap((banks: BankAccount[]) => {
          this.banks = banks;
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
    if (simpleChanges.transaction && this.transaction && this.transaction.id) {
      this.formMode = FormMode.Edit;
      this.transactionForm.patchValue(this.transaction);
      this.onSetFocus();
    } else {
      this.onReset();
    }
    if (
      this.formMode === FormMode.Add &&
      simpleChanges.mostRecentTransaction &&
      this.mostRecentTransaction &&
      this.mostRecentTransaction.id
    ) {
      this.transactionForm.patchValue(this.mostRecentTransaction);
      this.f['date'].setValue(null);
      this.f['note'].setValue(null);
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

  onSubmit() {
    this.submitted = true;

    // stop here if form is invalid
    if (this.transactionForm.invalid) {
      return;
    }

    this.saving = true;
    if (this.formMode == FormMode.Add) {
      this.createTransaction();
    } else {
      this.editTransaction();
    }
  }

  onReset() {
    this.saving = false;
    this.loading = false;
    this.submitted = false;
    this.formMode = FormMode.Add;
    this.alertService.clear();
    this.transactionForm.reset();
    if (this.member) {
      this.f['idmember'].setValue(this.member.id);
    }
  }

  onSetFocus() {
    setTimeout(() => {
      // this will make the execution after the above boolean has changed
      const el = document.getElementById('transactionDate');
      if (el) {
        el.focus();
      }
    }, 200);
  }

  private createTransaction() {
    this.transactionService
      .create(this.transactionForm.value)
      .subscribe({
        next: (result: any) => {
          this.alertService.success('Transaction added', {
            keepAfterRouteChange: false,
          });
        },
        error: (error) => {
          console.log(error);
          this.alertService.error('Unable to add new transaction', {
            keepAfterRouteChange: false,
          });
        },
      })
      .add(() => {
        this.reloadRequested.emit(this.transactionForm.value);
      });
  }

  private editTransaction() {
    if (!this.transaction || !this.transaction.id) {
      return;
    }

    this.transactionService
      .update(this.transaction.id, this.transactionForm.value)
      .subscribe({
        next: (result: any) => {
          this.alertService.success('Transaction updated', {
            keepAfterRouteChange: false,
          });
        },
        error: (error) => {
          console.log(error);
          this.alertService.error('Unable to update transaction.', {
            keepAfterRouteChange: false,
          });
        },
      })
      .add(() => {
        this.reloadRequested.emit(this.transaction);
      });
  }
}
