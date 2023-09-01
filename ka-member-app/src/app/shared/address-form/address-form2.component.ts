import {
    Component,
  } from '@angular/core';
  import {
    ControlValueAccessor,
    NG_VALUE_ACCESSOR,
  } from '@angular/forms';

import { Address} from '@app/_models';
  
  @Component({
    selector: 'choose-quantity',
    templateUrl: "choose-quantity.component.html",
    styleUrls: ["choose-quantity.component.scss"],
    providers: [
      {
        provide: NG_VALUE_ACCESSOR,
        multi:true,
        useExisting: AddressForm2Component
      }
    ]
  })
  export class AddressForm2Component implements ControlValueAccessor {
    address?: Address;

    onChange = (address: Address) => {};
    onTouched = () => {};
    touched = false;
    disabled = false;

    writeValue(address: Address) {
        this.address = address;
    }

    registerOnChange(onChange: any) {
        this.onChange = onChange;
    }

    registerOnTouched(onTouched: any) {
        this.onTouched = onTouched;
    }

    markAsTouched() {
        if (!this.touched) {
            this.onTouched();
            this.touched = true;
            }
    }

    setDisabledState(disabled: boolean) {
        this.disabled = disabled;
      }


  }