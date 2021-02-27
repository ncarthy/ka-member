import { Component, OnInit } from '@angular/core';
import {MemberSearchResult} from '@app/_models';

@Component({
  selector: 'member-search',
  templateUrl: './member-search.component.html',
  styleUrls: ['./member-search.component.css']
})
export class MemberSearchComponent implements OnInit {
  results: MemberSearchResult[];
  loading: boolean;

  constructor() { }

  ngOnInit(): void {
  }


  updateResults(results: MemberSearchResult[]): void {
    this.results = results;
    console.log("results:", this.results); // uncomment to take a look
  }
}
