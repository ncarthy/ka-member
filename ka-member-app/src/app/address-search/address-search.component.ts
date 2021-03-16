import { Component, OnInit, Output, EventEmitter } from '@angular/core';
import { Address } from '@app/_models';

@Component({
  selector: 'address-search',
  templateUrl: './address-search.component.html',
})
export class AddressSearchComponent implements OnInit {
  @Output() address: EventEmitter<Address> = new EventEmitter<Address>();
  @Output() manualEntry: EventEmitter<boolean> = new EventEmitter<boolean>();

  addresses!: Address[];
  loading: boolean = false; // set by 'address-search-box' component
  selectedAddress: Address = new Address();
  disable: boolean = false;

  constructor() { }

  ngOnInit(): void {}

  updateAddresses(results: Address[]): void {
    this.addresses = results;
  }

  onAddressChange(value: Address) {
    if (value) {
      this.selectedAddress = value;

      this.address.emit(this.selectedAddress);
    }
  }

  toggleManualEntry() {
    this.disable = !this.disable;
    this.manualEntry.emit(this.disable);
  }

}
