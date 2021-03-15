import { Component, OnInit } from '@angular/core';
import {Address, Country} from '@app/_models';
import {CountryService} from '@app/_services';

import { first } from 'rxjs/operators';

@Component({
  selector: 'address-search',
  templateUrl: './address-search.component.html'
})
export class AddressSearchComponent implements OnInit {
  addresses!: Address[];
  loading: boolean = false;
  countries!: Country[];
  address: Address = new Address();

  constructor(private countryService : CountryService) {   }

  ngOnInit(): void {
    this.address = new Address();    

    this.countryService.getAll()
    .pipe(first())
    .subscribe(x => {
      this.countries = x;
      const arr = x.filter((el) => el.name==='United Kingdom');
      this.address.country = x.filter(el => el.name==='United Kingdom')[0];
      });
  }

  updateAddresses(results: Address[]): void {
    this.addresses = results;
  }

  onCountryChange(value : Country) : void {
    this.address.country = this.countries.filter(el => el.name===value.name)[0];
  }
}
