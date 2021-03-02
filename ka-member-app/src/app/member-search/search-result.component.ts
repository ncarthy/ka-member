import {
    Component,
    OnInit,
    Input
} from '@angular/core';

import {MemberSearchResult } from '@app/_models';
    

 @Component({
    selector: 'member-search-result',
    templateUrl: './search-result.component.html',
    styleUrls: ['./search-result.component.css']
})
export class SearchResultComponent implements OnInit {
    @Input() member!: MemberSearchResult;    
    
    constructor() { }
    
    ngOnInit() {
     }
    
}