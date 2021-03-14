import { Component, OnInit } from '@angular/core';
import {Address} from '@app/_models';

@Component({
  selector: 'address-search',
  templateUrl: './address-search.component.html'
})
export class AddressSearchComponent implements OnInit {
  results!: Address[];
  loading: boolean = false;

  constructor() { }

  ngOnInit(): void {
  }

  updateResults(results: Address[]): void {
    this.results = results;
  }

}
