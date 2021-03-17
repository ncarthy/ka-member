/* Custom Form Control code taken from https://github.com/xiongemi/angular-form-ngxs/ */
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
  NG_VALUE_ACCESSOR,
  Validators,
} from '@angular/forms';
import { Subscription } from 'rxjs';
import { first } from 'rxjs/operators';

import { AddressFormValue } from './address-form-value.interface';
import { CountryService } from '@app/_services';
import { Address, Country } from '@app/_models';

@Component({
  selector: 'address-form',
  templateUrl: './address-form.component.html',
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => AddressFormComponent),
      multi: true,
    },
  ],
})
export class AddressFormComponent
  implements ControlValueAccessor, OnInit, OnDestroy, OnChanges {
  @Input() touched: boolean = false;
  @Input() address?: AddressFormValue;

  addresses!: Address[];
  countries!: Country[];
  uk!: Country;
  loading: boolean = false; // set by 'address-search-box' component
  onChange: any = (_: AddressFormValue) => {};
  onTouch: any = () => {};
  submitted: boolean = false;
  showFormFields: boolean = false;

  addressForm = this.fb.group({
    addressLine1: [null, Validators.required],
    addressLine2: [null],
    city: [null, Validators.required],
    county: [null],
    country: [null, Validators.required],
    postcode: [null, Validators.required],
  });

  private subscription = new Subscription();

  constructor(
    private fb: FormBuilder,
    private countryService: CountryService
  ) {}

  // convenience getter for easy access to form fields
  get f() {
    return this.addressForm.controls;
  }

  ngOnInit(): void {
    this.countryService
      .getAll()
      .pipe(first())
      .subscribe((countryArray) => {
        this.countries = countryArray;
        this.uk = countryArray.filter((c: Country) => c.name === 'United Kingdom')[0];
        if (this.address) {
          this.addressForm.controls['addressLine1'].setValue(this.address.addressLine1);
          this.addressForm.controls['addressLine2'].setValue(this.address.addressLine2);
          this.addressForm.controls['city'].setValue(this.address.city);
          this.addressForm.controls['county'].setValue(this.address.county);
          this.addressForm.controls['country'].setValue(this.address.country.id);
          this.addressForm.controls['postcode'].setValue(this.address.postcode);
          this.showFormFields = true;
        } else {
          this.addressForm.controls['country'].setValue(this.uk.id);
        }
      });

    this.subscription.add(
      this.addressForm.valueChanges.subscribe((value: AddressFormValue) => {
        this.onChange(value);
      })
    );
  }
  ngOnDestroy() {
    this.subscription.unsubscribe();
  }

  ngOnChanges(simpleChanges: SimpleChanges) {
    if (simpleChanges['touched'] && simpleChanges['touched'].currentValue) {
      this.addressForm.markAllAsTouched();
    }
  }

  writeValue(value: null | AddressFormValue): void {
    if (value) {
      this.addressForm.reset(value);
    }
  }

  registerOnChange(fn: () => {}): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: (_: AddressFormValue) => {}): void {
    this.onTouch = fn;
  }

  updateAddresses(results: Address[]): void {
    this.addresses = results;    
  }

  onAddressChange(address : Address) : void {
    this.showFormFields = true;
    this.addressForm.controls['addressLine1'].setValue(address.line1);
    this.addressForm.controls['addressLine2'].setValue(address.line2);
    this.addressForm.controls['city'].setValue(address.town);
    this.addressForm.controls['county'].setValue(address.county);
    this.addressForm.controls['postcode'].setValue(address.postcode);
    this.addressForm.controls['country'].setValue(address.country.id);
  }
}
