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

  constructor(private countryService : CountryService) {   }

  ngOnInit(): void {
    this.countryService.getAll()
    .pipe(first())
    .subscribe(x => this.countries = x);
  }

  updateAddresses(results: Address[]): void {
    this.addresses = results;
  }

}
