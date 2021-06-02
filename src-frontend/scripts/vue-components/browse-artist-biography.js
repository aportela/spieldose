const template = function () {
    return `
        <div class="columns">
            <div class="column is-8">
                <div class="content" >
                    <div class="card is-pulled-right ml-5 mb-5 pr-5" style="max-width: 18em;">
                        <div class="card-content online-hide-overflow">
                            <h6 v-if="officialWebLink"><i class="fas fa-sitemap"></i> Official web</h6>
                            <p v-if="officialWebLink"><a :href="officialWebLink" target="_blank">{{ officialWebLink }}</a></p>
                            <h6 v-if="youtubeLink"><i class="fab fa-youtube has-text-danger"></i> Youtube</h6>
                            <p v-if="youtubeLink"><a :href="youtubeLink" target="_blank">{{ youtubeLink }}</a></p>
                            <h6 v-if="spotifyLink"><i class="fab fa-spotify has-text-primary"></i> Spotify</h6>
                            <p v-if="spotifyLink"><a href="https://spotify.com" target="_blank">https://spotify.com</a></p>
                            <h6 v-if="wikipediaLink"><i class="fab fa-wikipedia-w"></i> Wikipedia</h6>
                            <p v-if="wikipediaLink"><a href="https://wikipedia.com" target="_blank">https://wikipedia.com</a></p>
                            <h6 v-if="lastFMLink"><i class="fab fa-lastfm has-text-danger"></i> Last.FM</h6>
                            <p v-if="lastFMLink"><a :href="lastFMLink" target="_blank">{{ lastFMLink }}</a></p>
                            <h6 v-if="musicBrainzId"><i class="fas fa-tag"></i></i> MusicBrainz</h6>
                            <p v-if="musicBrainzId"><a :href="'https://musicbrainz.org/artist/' + musicBrainzId" target="_blank">https://musicbrainz.org/artist/{{ musicBrainzId }}</a></p>

                            <h6 v-if="startLifeSpanYear">Years Active</h6>
                            <p v-if="startLifeSpanYear">{{ startLifeSpanYear }} <span v-if="endLifeSpanYear"> - {{ endLifeSpanYear }}</span><span v-else> – present</span> ({{ totalYearsActive }} years)</p>
                            <h6 v-if="foundedIn">Founded in</h6>
                            <p v-if="foundedIn">{{ foundedIn }}</p>
                            <div v-if="membersOfBand && membersOfBand.length > 0">
                                <h6 v-if="membersOfBand">Members</h6>
                                <p class="mb-0" v-for="member in membersOfBand">
                                {{ member.name }} - {{ member.attribute }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-html="biography">
                    </div>
                </div>
            </div>
            <div class="column is-4">
                <div id="similar">
                    <div class="is-clearfix">
                        <span class="title is-5 is-pulled-left">Similar to</span>
                        <span class="is-pulled-right">Show more <i class="fas fa-angle-right"></i></span>
                    </div>
                    <div class="columns is-size-6">
                        <div class="column is-4 has-text-grey is-centered" v-for="similar, idx in similarArtists" :key="similar.name" v-show="idx < 3">
                            <figure class="image is-96x96" style="margin: 0px auto;">
                                <img class="is-rounded" src="api/thumbnail?url=https://lastfm-img2.akamaized.net/i/u/300x300/1a3adf2f20b642c3bc50b10048b980a6.png">
                            </figure>
                            <p class="has-text-centered has-text-grey">{{ similar.name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
`;
};


export default {
    name: 'spieldose-browse-artist-biography',
    template: template(),
    props: [
        'artist'
    ],
    computed: {
        biography: function() {
            return((this.artist && this.artist.lastFM && this.artist.lastFM.artist && this.artist.lastFM.artist.bio && this.artist.lastFM.artist.bio.content) ? this.artist.lastFM.artist.bio.content.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2'): null);
        },
        musicBrainzId: function() {
            return((this.artist && this.artist.musicBrainz && this.artist.musicBrainz.id) ? this.artist.musicBrainz.id: null);
        },
        officialWebLink: function() {
            if (this.artist && this.artist.musicBrainz && this.artist.musicBrainz.relations) {
                const relation = this.artist.musicBrainz.relations.find((relation) => relation.type == 'official homepage');
                return((relation && relation.url && relation.url.resource) ? relation.url.resource: null);
            } else {
                return(null);
            }
        },
        youtubeLink: function() {
            if (this.artist && this.artist.musicBrainz && this.artist.musicBrainz.relations) {
                const relation = this.artist.musicBrainz.relations.find((relation) => relation.type == 'youtube');
                return((relation && relation.url && relation.url.resource) ? relation.url.resource: null);
            } else {
                return(null);
            }
        },
        lastFMLink: function() {
            if (this.artist && this.artist.musicBrainz && this.artist.musicBrainz.relations) {
                const relation = this.artist.musicBrainz.relations.find((relation) => relation.type == 'last.fm');
                return((relation && relation.url && relation.url.resource) ? relation.url.resource: null);
            } else {
                return(null);
            }
        },
        wikipediaLink: function() {
            return(null);
        },
        spotifyLink: function() {
            return(null);
        },
        startLifeSpanYear: function() {
            if (this.artist && this.artist.musicBrainz && this.artist.musicBrainz['life-span'] && this.artist.musicBrainz['life-span'].begin) {
                return(this.artist.musicBrainz['life-span'].begin.substring(0, 4));
            } else {
                return(null);
            }
        },
        endLifeSpanYear: function() {
            if (this.artist && this.artist.musicBrainz && this.artist.musicBrainz['life-span'] && this.artist.musicBrainz['life-span'].end) {
                return(this.artist.musicBrainz['life-span'].end.substring(0, 4));
            } else {
                return(null);
            }
        },
        totalYearsActive: function() {
            return(this.endLifeSpanYear ? this.endLifeSpanYear - this.startLifeSpanYear: new Date().getFullYear() - this.startLifeSpanYear);
        },
        foundedIn: function() {
            if (this.artist && this.artist.musicBrainz) {
                let arra = [];
                let beginArea = this.artist.musicBrainz['begin-area'] && this.artist.musicBrainz['begin-area'].name ? this.artist.musicBrainz['begin-area'].name: null;
                if (beginArea) {
                    arra.push(beginArea);
                }
                let area = this.artist.musicBrainz['area'] && this.artist.musicBrainz['area'].name ? this.artist.musicBrainz['area'].name: null;
                if (area) {
                    arra.push(area);
                }
                if (arra.length > 0) {
                    return(arra.join(', '));
                } else {
                    return(null);
                }
            } else {
                return(null);
            }
        },
        membersOfBand: function() {
            if (this.artist && this.artist.musicBrainz && this.artist.musicBrainz.relations) {
                return(
                    this.artist.musicBrainz.relations.filter((relation) => relation.type == 'member of band').map((member) => {
                        return(
                            {
                                name: member.artist ? member.artist.name : null,
                                attribute: member.attributes ? member.attributes[0]: null
                            }
                        );
                    })
                );
            } else {
                return(null);
            }
        },
        similarArtists: function() {
            if (this.artist && this.artist.lastFM && this.artist.lastFM.artist && this.artist.lastFM.artist.similar && this.artist.lastFM.artist.similar.artist) {
                return(this.artist.lastFM.artist.similar.artist);
            } else {
                return(null);
            }
        }
    }
}