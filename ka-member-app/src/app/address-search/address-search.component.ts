import { 
  Component, 
  OnInit,    
  Output,
  EventEmitter 
} from '@angular/core';

import {Address} from '@app/_models';

@Component({
  selector: 'address-search',
  templateUrl: './address-search.component.html'
})
export class AddressSearchComponent implements OnInit {
  @Output() address: EventEmitter<Address> = new EventEmitter<Address>();

  addresses!: Address[];
  loading: boolean = false;
  selectedAddress: Address = new Address();
  query!: string;

  constructor() {   }

  ngOnInit(): void {  }

  updateAddresses(results: Address[]): void {
    this.addresses = results;
  }

  onAddressChange(value: Address) { 
      this.selectedAddress = value;
      
      this.address.emit(this.selectedAddress);
    }
}
