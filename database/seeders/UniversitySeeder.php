<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Country;

class UniversitySeeder extends Seeder
{
    public function run()
    {
        // Get actual country IDs from the database
        $us = Country::where('country_code', 'US')->first();
        $ca = Country::where('country_code', 'CA')->first();
        $uk = Country::where('country_code', 'UK')->first();
        $au = Country::where('country_code', 'AU')->first();
        $de = Country::where('country_code', 'DE')->first();
        $fr = Country::where('country_code', 'FR')->first();
        $jp = Country::where('country_code', 'JP')->first();
        $kr = Country::where('country_code', 'KR')->first();
        $cn = Country::where('country_code', 'CN')->first();
        $in = Country::where('country_code', 'IN')->first();
        $br = Country::where('country_code', 'BR')->first();
        $mx = Country::where('country_code', 'MX')->first();
        $it = Country::where('country_code', 'IT')->first();
        $es = Country::where('country_code', 'ES')->first();
        $nl = Country::where('country_code', 'NL')->first();
        $se = Country::where('country_code', 'SE')->first();
        $ch = Country::where('country_code', 'CH')->first();
        $sg = Country::where('country_code', 'SG')->first();
        $my = Country::where('country_code', 'MY')->first();
        $ae = Country::where('country_code', 'AE')->first();
        $sa = Country::where('country_code', 'SA')->first();
        $eg = Country::where('country_code', 'EG')->first();
        $za = Country::where('country_code', 'ZA')->first();
        $tr = Country::where('country_code', 'TR')->first();
        $ru = Country::where('country_code', 'RU')->first();
        $ua = Country::where('country_code', 'UA')->first();
        $ar = Country::where('country_code', 'AR')->first();
        $cl = Country::where('country_code', 'CL')->first();
        $nz = Country::where('country_code', 'NZ')->first();
        $ie = Country::where('country_code', 'IE')->first();

        $universities = [
            // United States
            ['Harvard University', 'Cambridge', $us->country_id, true],
            ['Stanford University', 'Stanford', $us->country_id, true],
            ['Massachusetts Institute of Technology', 'Cambridge', $us->country_id, true],
            ['California Institute of Technology', 'Pasadena', $us->country_id, true],
            ['University of California, Berkeley', 'Berkeley', $us->country_id, true],
            ['Yale University', 'New Haven', $us->country_id, true],
            ['Princeton University', 'Princeton', $us->country_id, true],
            ['Columbia University', 'New York', $us->country_id, true],
            ['University of Chicago', 'Chicago', $us->country_id, true],
            ['University of Michigan', 'Ann Arbor', $us->country_id, true],

            // Canada
            ['University of Toronto', 'Toronto', $ca->country_id, true],
            ['University of British Columbia', 'Vancouver', $ca->country_id, true],
            ['McGill University', 'Montreal', $ca->country_id, true],
            ['University of Alberta', 'Edmonton', $ca->country_id, true],
            ['University of Waterloo', 'Waterloo', $ca->country_id, true],
            ['University of Calgary', 'Calgary', $ca->country_id, true],

            // United Kingdom
            ['University of Oxford', 'Oxford', $uk->country_id, true],
            ['University of Cambridge', 'Cambridge', $uk->country_id, true],
            ['Imperial College London', 'London', $uk->country_id, true],
            ['London School of Economics', 'London', $uk->country_id, true],
            ['University College London', 'London', $uk->country_id, true],
            ['University of Edinburgh', 'Edinburgh', $uk->country_id, true],
            ['University of Manchester', 'Manchester', $uk->country_id, true],

            // Australia
            ['University of Melbourne', 'Melbourne', $au->country_id, true],
            ['Australian National University', 'Canberra', $au->country_id, true],
            ['University of Sydney', 'Sydney', $au->country_id, true],
            ['University of Queensland', 'Brisbane', $au->country_id, true],
            ['University of New South Wales', 'Sydney', $au->country_id, true],

            // Germany
            ['Technical University of Munich', 'Munich', $de->country_id, true],
            ['Ludwig Maximilian University of Munich', 'Munich', $de->country_id, true],
            ['Heidelberg University', 'Heidelberg', $de->country_id, true],
            ['Humboldt University of Berlin', 'Berlin', $de->country_id, true],
            ['Free University of Berlin', 'Berlin', $de->country_id, true],

            // France
            ['University of Paris-Saclay', 'Paris', $fr->country_id, true],
            ['Sorbonne University', 'Paris', $fr->country_id, true],
            ['École Polytechnique', 'Palaiseau', $fr->country_id, true],
            ['PSL University', 'Paris', $fr->country_id, true],

            // Japan
            ['University of Tokyo', 'Tokyo', $jp->country_id, true],
            ['Kyoto University', 'Kyoto', $jp->country_id, true],
            ['Tokyo Institute of Technology', 'Tokyo', $jp->country_id, true],
            ['Osaka University', 'Osaka', $jp->country_id, true],

            // South Korea
            ['Seoul National University', 'Seoul', $kr->country_id, true],
            ['Korea Advanced Institute of Science & Technology', 'Daejeon', $kr->country_id, true],
            ['Pohang University of Science and Technology', 'Pohang', $kr->country_id, true],
            ['Yonsei University', 'Seoul', $kr->country_id, true],

            // China
            ['Tsinghua University', 'Beijing', $cn->country_id, true],
            ['Peking University', 'Beijing', $cn->country_id, true],
            ['Zhejiang University', 'Hangzhou', $cn->country_id, true],
            ['Shanghai Jiao Tong University', 'Shanghai', $cn->country_id, true],

            // India
            ['Indian Institute of Technology Bombay', 'Mumbai', $in->country_id, true],
            ['Indian Institute of Science', 'Bangalore', $in->country_id, true],
            ['University of Delhi', 'Delhi', $in->country_id, true],
            ['Indian Institute of Technology Delhi', 'Delhi', $in->country_id, true],

            // Brazil
            ['University of São Paulo', 'São Paulo', $br->country_id, true],
            ['State University of Campinas', 'Campinas', $br->country_id, true],
            ['Federal University of Rio de Janeiro', 'Rio de Janeiro', $br->country_id, true],

            // Mexico
            ['National Autonomous University of Mexico', 'Mexico City', $mx->country_id, true],
            ['Monterrey Institute of Technology', 'Monterrey', $mx->country_id, true],

            // Italy
            ['University of Bologna', 'Bologna', $it->country_id, true],
            ['Sapienza University of Rome', 'Rome', $it->country_id, true],
            ['University of Milan', 'Milan', $it->country_id, true],

            // Spain
            ['University of Barcelona', 'Barcelona', $es->country_id, true],
            ['Complutense University of Madrid', 'Madrid', $es->country_id, true],
            ['Autonomous University of Madrid', 'Madrid', $es->country_id, true],

            // Netherlands
            ['University of Amsterdam', 'Amsterdam', $nl->country_id, true],
            ['Utrecht University', 'Utrecht', $nl->country_id, true],
            ['Leiden University', 'Leiden', $nl->country_id, true],

            // Sweden
            ['Karolinska Institute', 'Stockholm', $se->country_id, true],
            ['Uppsala University', 'Uppsala', $se->country_id, true],
            ['Lund University', 'Lund', $se->country_id, true],

            // Switzerland
            ['ETH Zurich', 'Zurich', $ch->country_id, true],
            ['École Polytechnique Fédérale de Lausanne', 'Lausanne', $ch->country_id, true],
            ['University of Zurich', 'Zurich', $ch->country_id, true],

            // Singapore
            ['National University of Singapore', 'Singapore', $sg->country_id, true],
            ['Nanyang Technological University', 'Singapore', $sg->country_id, true],

            // Malaysia
            ['University of Malaya', 'Kuala Lumpur', $my->country_id, true],
            ['Universiti Teknologi Malaysia', 'Johor Bahru', $my->country_id, true],

            // United Arab Emirates
            ['Khalifa University', 'Abu Dhabi', $ae->country_id, true],
            ['United Arab Emirates University', 'Al Ain', $ae->country_id, true],

            // Saudi Arabia
            ['King Saud University', 'Riyadh', $sa->country_id, true],
            ['King Abdullah University of Science and Technology', 'Thuwal', $sa->country_id, true],

            // Egypt
            ['Cairo University', 'Cairo', $eg->country_id, true],
            ['Alexandria University', 'Alexandria', $eg->country_id, true],

            // South Africa
            ['University of Cape Town', 'Cape Town', $za->country_id, true],
            ['University of the Witwatersrand', 'Johannesburg', $za->country_id, true],

            // Turkey
            ['Middle East Technical University', 'Ankara', $tr->country_id, true],
            ['Istanbul University', 'Istanbul', $tr->country_id, true],

            // Russia (inactive country)
            ['Lomonosov Moscow State University', 'Moscow', $ru->country_id, false],
            ['Saint Petersburg State University', 'Saint Petersburg', $ru->country_id, false],

            // Ukraine (inactive country)
            ['Taras Shevchenko National University', 'Kyiv', $ua->country_id, false],

            // Argentina
            ['University of Buenos Aires', 'Buenos Aires', $ar->country_id, true],

            // Chile
            ['University of Chile', 'Santiago', $cl->country_id, true],

            // New Zealand
            ['University of Auckland', 'Auckland', $nz->country_id, true],
            ['University of Otago', 'Dunedin', $nz->country_id, true],

            // Ireland
            ['Trinity College Dublin', 'Dublin', $ie->country_id, true],
            ['University College Dublin', 'Dublin', $ie->country_id, true],
        ];

        foreach ($universities as $university) {
            DB::table('universities')->insert([
                'university_name' => $university[0],
                'city' => $university[1],
                'country_id' => $university[2],
                'is_active' => $university[3],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ ' . count($universities) . ' universities created successfully!');
    }
}