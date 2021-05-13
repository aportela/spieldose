const template = function () {
    return `
        <div class="columns">
            <div class="column is-8">
                <div class="content" >
                    <div class="card is-pulled-right ml-5 mb-5" style="max-width: 18em;">
                        <div class="card-content">
                            <h6><i class="fas fa-sitemap"></i> Official web</h6>
                            <p><a href="https://foo.ba.r" target="_blank">https://foo.ba.r</a></p>
                            <h6><i class="fab fa-youtube has-text-danger"></i> Youtube</h6>
                            <p><a href="https://youtube.com" target="_blank">https://youtube.com</a></p>
                            <h6><i class="fab fa-spotify has-text-primary"></i> Spotify</h6>
                            <p><a href="https://spotify.com" target="_blank">https://spotify.com</a></p>
                            <h6><i class="fab fa-wikipedia-w"></i> Wikipedia</h6>
                            <p><a href="https://wikipedia.com" target="_blank">https://wikipedia.com</a></p>
                            <h6>Years Active</h6>
                            <p>1970 – present (xx years)</p>
                            <h6>Founded in</h6>
                            <p>My town, my city</p>
                            <h6>Members</h6>
                            <p>John Doe (1970 – 1996)
                            <br>Jane Doe (1971 – 2004)
                            <br>Mr President (1981 – present)
                            </p>
                        </div>
                    </div>
                    <div v-html="artist.bio">
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
                        <div class="column is-4 has-text-grey is-centered">
                            <figure class="image is-96x96" style="margin: 0px auto;">
                                <img class="is-rounded" src="api/thumbnail?url=https://lastfm-img2.akamaized.net/i/u/300x300/1a3adf2f20b642c3bc50b10048b980a6.png">
                            </figure>
                            <p class="has-text-centered has-text-grey">Artist1</p>
                        </div>
                        <div class="column is-4">
                            <figure class="image is-96x96" style="margin: 0px auto;">
                                <img class="is-rounded" src="api/thumbnail?url=https://lastfm-img2.akamaized.net/i/u/300x300/1a3adf2f20b642c3bc50b10048b980a6.png">
                            </figure>
                            <p class="has-text-centered has-text-grey">Artist</p>
                        </div>
                        <div class="column is-4">
                            <figure class="image is-96x96" style="margin: 0px auto;">
                                <img class="is-rounded" src="api/thumbnail?url=https://lastfm-img2.akamaized.net/i/u/300x300/1a3adf2f20b642c3bc50b10048b980a6.png">
                            </figure>
                            <p class="has-text-centered has-text-grey">Artist</p>
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
    ]
}